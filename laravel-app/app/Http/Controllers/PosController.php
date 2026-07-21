<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PosController extends Controller
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $products = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->select(
                'products.product_id',
                'products.product_name',
                'products.price',
                'products.stock',
                'products.status',
                'categories.category_id',
                'categories.category_name',
                'categories.zone',
                'categories.is_stock_tracked'
            )
            ->orderBy('categories.zone')
            ->orderBy('categories.category_name')
            ->orderBy('products.product_name')
            ->get()
            ->map(fn($row) => [
                'id'          => (int) $row->product_id,
                'name'        => $row->product_name,
                'price'       => (float) $row->price,
                'category'    => strtolower($row->category_name),
                'category_id' => (int) $row->category_id,
                'zone'        => strtolower($row->zone),
                'tracked'     => (bool) $row->is_stock_tracked,
                'stock'       => $row->stock,
                'status'      => $row->status,
            ])->toArray();

        $last_inv = DB::table('sales_transactions')->max('transaction_id');
        $last_inv = $last_inv ? intval($last_inv) + 1 : 1;
        $invoice_number = str_pad($last_inv, 11, '0', STR_PAD_LEFT);

        $cashier_name = session('username') ?? session('fullname') ?? 'CASHIER';

        return view('pos.index', compact('products', 'last_inv', 'invoice_number', 'cashier_name'));
    }

    public function verifyManager(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = DB::table('users')->where('username', $data['username'])->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials.']);
        }

        if ($user->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Account is inactive.']);
        }

        if (!Hash::check($data['password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials.']);
        }

        if (!in_array($user->role, ['tl', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Not authorized. TL or Manager only.']);
        }

        return response()->json([
            'success' => true,
            'manager_name' => $user->fullname ?? $user->username,
        ]);
    }

    public function logVoid(Request $request)
    {
        $data = $request->validate([
            'item_name'     => 'required|string',
            'price'         => 'required|numeric',
            'qty'           => 'required|integer|min:1',
            'authorized_by' => 'nullable|string',
        ]);

        DB::table('voided_items')->insert([
            'item_name'     => $data['item_name'],
            'price'         => $data['price'],
            'qty'           => $data['qty'],
            'amount'        => $data['price'] * $data['qty'],
            'cashier_name'  => session('username') ?? session('fullname'),
            'authorized_by' => $data['authorized_by'] ?? null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function saveTransaction(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['success' => false, 'message' => 'Not authenticated. Please log in again.'], 401);
        }

        if ($this->isDayLocked()) {
            return response()->json([
                'success' => false,
                'locked'  => true,
                'message' => 'Transactions are closed for today after cut-off. Please try again tomorrow.',
            ]);
        }

        $data = $request->validate([
            'customer_name'    => 'nullable|string',
            'service_type'     => 'nullable|string',
            'payment_method'   => 'nullable|string',
            'reference_number' => 'nullable|string',
            'gross'            => 'required|numeric',
            'discount'         => 'nullable|numeric',
            'tax'              => 'nullable|numeric',
            'total'            => 'required|numeric',
            'tendered'         => 'nullable|numeric',
            'vatable_sales'        => 'nullable|numeric',
            'vat_exempt_sales'     => 'nullable|numeric',
            'pwd_senior_type'      => 'nullable|string|in:Senior Citizen,PWD',
            'pwd_senior_name'      => 'nullable|string',
            'pwd_senior_id_number' => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.id'       => 'required|integer',
            'items.*.name'     => 'required|string',
            'items.*.price'    => 'required|numeric',
            'items.*.qty'      => 'required|integer|min:1',
        ]);

        $cashierId    = (int) session('user_id');
        $customerName = trim($data['customer_name'] ?? '') ?: 'Walk-in Customer';
        $serviceType  = trim($data['service_type'] ?? 'Walk-In');

        $paymentMap = [
            'cash'      => 'Cash',
            'card'      => 'Card',
            'gcash'     => 'GCash',
            'maya'      => 'Maya',
            'stardeals' => 'StarDeals',
            'klook'     => 'Klook',
        ];
        $rawPayment    = strtolower(trim($data['payment_method'] ?? 'cash'));
        $paymentMethod = $paymentMap[$rawPayment] ?? ucfirst($rawPayment);

        $referenceNumber = !empty($data['reference_number']) ? trim($data['reference_number']) : null;

        // === PWD/Senior + VAT breakdown ===
        $vatableSales   = $data['vatable_sales']    ?? null;
        $vatExemptSales = $data['vat_exempt_sales'] ?? null;
        $pwdSeniorType  = $data['pwd_senior_type']      ?? null;
        $pwdSeniorName  = $data['pwd_senior_name']      ?? null;
        $pwdSeniorIdNum = $data['pwd_senior_id_number'] ?? null;
        // ===================================

        $gross        = (float) ($data['gross']    ?? 0);
        $discount     = (float) ($data['discount'] ?? 0);
        $vat          = (float) ($data['tax']      ?? 0);
        $total        = (float) ($data['total']    ?? 0);
        $cashReceived = (float) ($data['tendered'] ?? $total);
        $changeAmount = max(0, $cashReceived - $total);
        $subtotal     = $gross - $discount;
        $items        = $data['items'];

        try {
            $transactionId = DB::transaction(function () use (
                $cashierId,
                $customerName,
                $serviceType,
                $subtotal,
                $discount,
                $vat,
                $total,
                $paymentMethod,
                $referenceNumber,
                $cashReceived,
                $changeAmount,
                $items,
                $vatableSales,
                $vatExemptSales,
                $pwdSeniorType,
                $pwdSeniorName,
                $pwdSeniorIdNum
            ) {
                $transactionId = DB::table('sales_transactions')->insertGetId([
                    'cashier_id'         => $cashierId,
                    'customer_name'      => $customerName,
                    'service_type'       => $serviceType,
                    'subtotal'           => $subtotal,
                    'discount_amount'    => $discount,
                    'vat_amount'         => $vat,
                    'total_amount'       => $total,
                    'payment_method'     => $paymentMethod,
                    'reference_number'   => $referenceNumber,
                    'cash_received'      => $cashReceived,
                    'change_amount'      => $changeAmount,
                    'transaction_status' => 'Completed',
                    'vatable_sales'        => $vatableSales,
                    'vat_exempt_sales'     => $vatExemptSales,
                    'pwd_senior_type'      => $pwdSeniorType,
                    'pwd_senior_name'      => $pwdSeniorName,
                    'pwd_senior_id_number' => $pwdSeniorIdNum,
                    'created_at'         => now(),
                ]);

                foreach ($items as $item) {
                    $price = (float) $item['price'];
                    $qty   = (int)   $item['qty'];

                    DB::table('transaction_items')->insert([
                        'transaction_id' => $transactionId,
                        'product_id'     => (int) $item['id'],
                        'product_name'   => trim($item['name']),
                        'price'          => $price,
                        'quantity'       => $qty,
                        'subtotal'       => $price * $qty,
                    ]);

                    // Bawas stock ng products na naka-tag sa mga category
                    // na is_stock_tracked = 1 sa categories table.
                    // Zone/category-agnostic na — awtomatikong susunod ang
                    // bagong categories na idadagdag mula sa Inventory.
                    DB::table('products')
                        ->where('product_id', (int) $item['id'])
                        ->whereIn('category_id', function ($q) {
                            $q->select('category_id')->from('categories')->where('is_stock_tracked', 1);
                        })
                        ->whereNotNull('stock')
                        ->where('stock', '>', 0)
                        ->decrement('stock', $qty);
                }

                return $transactionId;
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save transaction: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success'        => true,
            'transaction_id' => $transactionId,
            'message'        => 'Transaction saved successfully.',
        ]);
    }

    public function getTransaction(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $invoice = $request->query('invoice');
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice number required.']);
        }

        $transactionId = (int) $invoice;

        $txn = DB::table('sales_transactions as t')
            ->leftJoin('users as u', 'u.user_id', '=', 't.cashier_id')
            ->where('t.transaction_id', $transactionId)
            ->select('t.*', 'u.fullname as cashier_name')
            ->first();

        if (!$txn) {
            return response()->json(['success' => false, 'message' => 'Invoice #' . $invoice . ' not found.']);
        }

        $items = DB::table('transaction_items')
            ->where('transaction_id', $transactionId)
            ->get()
            ->map(fn($i) => [
                'name'  => $i->product_name,
                'price' => (float) $i->price,
                'qty'   => (int)   $i->quantity,
            ])->toArray();

        return response()->json([
            'success'     => true,
            'transaction' => [
                'transaction_id'   => $txn->transaction_id,
                'invoice_number'   => str_pad($txn->transaction_id, 11, '0', STR_PAD_LEFT),
                'customer_name'    => $txn->customer_name,
                'service_type'     => $txn->service_type,
                'cashier_name'     => $txn->cashier_name ?? session('username'),
                'payment_method'   => $txn->payment_method,
                'reference_number' => $txn->reference_number ?? null,
                'gross'            => (float) $txn->subtotal + (float) $txn->discount_amount,
                'discount'         => (float) $txn->discount_amount,
                'total'            => (float) $txn->total_amount,
                'tendered'         => (float) ($txn->cash_received ?? $txn->total_amount),
                'created_at'       => $txn->created_at,
                'items'            => $items,
                'vatable_sales'        => (float) ($txn->vatable_sales ?? ($txn->total_amount / 1.12)),
                'vat_exempt_sales'     => (float) ($txn->vat_exempt_sales ?? 0),
                'pwd_senior_type'      => $txn->pwd_senior_type ?? null,
                'pwd_senior_name'      => $txn->pwd_senior_name ?? null,
                'pwd_senior_id_number' => $txn->pwd_senior_id_number ?? null,
            ],
        ]);
    }

    // ================= CASHFLOW =================
    public function getCashflow(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        $cashSales = DB::table('sales_transactions')
            ->whereDate('created_at', $date)
            ->where('payment_method', 'cash')
            ->sum('total');

        $movementsIn = DB::table('cash_movements')
            ->whereDate('created_at', $date)->where('type', 'in')->sum('amount');
        $movementsOut = DB::table('cash_movements')
            ->whereDate('created_at', $date)->where('type', 'out')->sum('amount');

        $movements = DB::table('cash_movements')
            ->whereDate('created_at', $date)->orderByDesc('created_at')->get();

        return response()->json([
            'rows' => $movements->map(fn($m) => [
                'date' => $m->created_at,
                'description' => $m->description,
                'cash_in'  => $m->type === 'in'  ? $m->amount : 0,
                'cash_out' => $m->type === 'out' ? $m->amount : 0,
            ]),
            'cash_sales' => $cashSales,
            'net' => ($cashSales + $movementsIn) - $movementsOut,
        ]);
    }

    public function addCashMovement(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:191',
        ]);

        DB::table('cash_movements')->insert([
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'user_id' => session('user_id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // ================= SALES REPORT HELPER =================
    private function getItemsByTransaction(\Illuminate\Support\Collection $transactionIds)
    {
        return DB::table('transaction_items')
            ->whereIn('transaction_id', $transactionIds)
            ->get()
            ->groupBy('transaction_id')
            ->map(function ($items) {
                return $items->map(fn($i) => [
                    'name'  => $i->product_name,
                    'price' => (float) $i->price,
                    'qty'   => (int)   $i->quantity,
                ])->toArray();
            })->toArray();
    }

    private function buildSalesReport(\Illuminate\Support\Collection $transactions, array $itemsByTxn)
    {
        $overallItems = [];
        $overallTotal = 0;
        $overallDiscount = 0;
        $overallPayments = ['cash' => 0, 'card' => 0, 'gcash' => 0, 'maya' => 0, 'stardeals' => 0, 'klook' => 0];
        $byCashier = [];

        foreach ($transactions as $t) {
            $items = $itemsByTxn[$t->transaction_id] ?? [];
            $cashierName = $t->cashier_name ?? 'Unknown';

            if (!isset($byCashier[$cashierName])) {
                $byCashier[$cashierName] = [
                    'cashier_name' => $cashierName,
                    'items' => [],
                    'total_sales' => 0,
                    'total_discount' => 0,
                    'klook_total' => 0,
                    'stardeals_total' => 0,
                    'payment_breakdown' => ['cash' => 0, 'card' => 0, 'gcash' => 0, 'maya' => 0, 'stardeals' => 0, 'klook' => 0],
                ];
            }

            foreach ($items as $it) {
                $name = $it['name'];
                $qty = $it['qty'];
                $line = $it['price'] * $it['qty'];

                if (!isset($overallItems[$name])) $overallItems[$name] = ['name' => $name, 'qty' => 0, 'total' => 0];
                $overallItems[$name]['qty'] += $qty;
                $overallItems[$name]['total'] += $line;

                if (!isset($byCashier[$cashierName]['items'][$name])) {
                    $byCashier[$cashierName]['items'][$name] = ['name' => $name, 'qty' => 0, 'total' => 0];
                }
                $byCashier[$cashierName]['items'][$name]['qty'] += $qty;
                $byCashier[$cashierName]['items'][$name]['total'] += $line;
            }

            $overallTotal += $t->total_amount;
            $overallDiscount += $t->discount_amount;
            $pm = strtolower($t->payment_method ?: 'cash');
            if (isset($overallPayments[$pm])) $overallPayments[$pm] += $t->total_amount;

            $byCashier[$cashierName]['total_sales'] += $t->total_amount;
            $byCashier[$cashierName]['total_discount'] += $t->discount_amount;
            if ($pm === 'klook') $byCashier[$cashierName]['klook_total'] += $t->total_amount;
            if ($pm === 'stardeals') $byCashier[$cashierName]['stardeals_total'] += $t->total_amount;
            if (isset($byCashier[$cashierName]['payment_breakdown'][$pm])) {
                $byCashier[$cashierName]['payment_breakdown'][$pm] += $t->total_amount;
            }
        }

        return [
            'overall' => [
                'items' => array_values($overallItems),
                'total_sales' => $overallTotal,
                'total_discount' => $overallDiscount,
                'payment_breakdown' => $overallPayments,
            ],
            'cashiers' => array_map(function ($c) {
                $c['items'] = array_values($c['items']);
                return $c;
            }, array_values($byCashier)),
        ];
    }

    // ================= PRINT SALES REPORT (cut-off) =================
    public function printSalesReportData(Request $request)
    {
        $authorizedBy = $request->input('authorized_by') ?: (session('username') ?? 'Manager');
        $lastCutoff = DB::table('sales_cutoffs')->orderByDesc('id')->first();
        $periodStart = $lastCutoff->period_end ?? now()->startOfDay();
        $periodEnd = now();

        $transactions = DB::table('sales_transactions as t')
            ->leftJoin('users as u', 'u.user_id', '=', 't.cashier_id')
            ->whereNull('t.cutoff_id')
            ->select('t.*', 'u.fullname as cashier_name')
            ->get();

        $voidedItems = DB::table('voided_items')->whereNull('cutoff_id')->get();

        if ($transactions->isEmpty() && $voidedItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No new transactions have been made yet.']);
        }

        $cutoffNumber = ($lastCutoff->cutoff_number ?? 0) + 1;

        $cutoffId = DB::table('sales_cutoffs')->insertGetId([
            'cutoff_number' => $cutoffNumber,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'performed_by' => session('user_id'),
            'performed_by_name' => $authorizedBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sales_transactions')
            ->whereIn('transaction_id', $transactions->pluck('transaction_id'))
            ->update(['cutoff_id' => $cutoffId]);

        DB::table('voided_items')
            ->whereIn('id', $voidedItems->pluck('id'))
            ->update(['cutoff_id' => $cutoffId]);

        $itemsByTxn = $this->getItemsByTransaction($transactions->pluck('transaction_id'));

        return response()->json([
            'success' => true,
            'cutoff_number' => $cutoffNumber,
            'period_end' => $periodEnd,
            'authorized_by' => $authorizedBy,
            'void_count' => $voidedItems->count(),
            'void_total' => $voidedItems->sum('amount'),
            'report' => $this->buildSalesReport($transactions, $itemsByTxn),
        ]);
    }

    public function presentReport()
    {
        $transactions = DB::table('sales_transactions as t')
            ->leftJoin('users as u', 'u.user_id', '=', 't.cashier_id')
            ->whereNull('t.cutoff_id')
            ->select('t.*', 'u.fullname as cashier_name')
            ->get();

        $voidedItems = DB::table('voided_items')->whereNull('cutoff_id')->get();

        if ($transactions->isEmpty() && $voidedItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No sales have been recorded today yet.']);
        }

        $itemsByTxn = $this->getItemsByTransaction($transactions->pluck('transaction_id'));

        return response()->json([
            'success' => true,
            'void_count' => $voidedItems->count(),
            'void_total' => $voidedItems->sum('amount'),
            'report' => $this->buildSalesReport($transactions, $itemsByTxn),
        ]);
    }

    public function historicalCutoffs(Request $request)
    {
        $date = $request->query('date');
        $q = DB::table('sales_cutoffs')->orderByDesc('id');
        if ($date) $q->whereDate('period_end', $date);
        return response()->json(['cutoffs' => $q->get()]);
    }

    public function cutoffDetail(int $id)
    {
        $cutoff = DB::table('sales_cutoffs')->find($id);
        if (!$cutoff) return response()->json(['success' => false, 'message' => 'Not found.']);

        $transactions = DB::table('sales_transactions as t')
            ->leftJoin('users as u', 'u.user_id', '=', 't.cashier_id')
            ->where('t.cutoff_id', $id)
            ->select('t.*', 'u.fullname as cashier_name')
            ->get();

        $voidedItems = DB::table('voided_items')->where('cutoff_id', $id)->get();

        $itemsByTxn = $this->getItemsByTransaction($transactions->pluck('transaction_id'));

        return response()->json([
            'success' => true,
            'cutoff_number' => $cutoff->cutoff_number,
            'period_end' => $cutoff->period_end,
            'authorized_by' => $cutoff->performed_by_name,
            'void_count' => $voidedItems->count(),
            'void_total' => $voidedItems->sum('amount'),
            'report' => $this->buildSalesReport($transactions, $itemsByTxn),
        ]);
    }
    // ================= DAY LOCK (cut-off) =================
    private function isDayLocked(): bool
    {
        return DB::table('sales_cutoffs')
            ->whereDate('period_end', now()->toDateString())
            ->exists();
    }

    public function cutoffStatus()
    {
        $locked = $this->isDayLocked();
        return response()->json([
            'locked'  => $locked,
            'message' => $locked
                ? 'Transactions are closed for today after cut-off. New transactions open tomorrow.'
                : null,
        ]);
    }
}