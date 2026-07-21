<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class InventoryController extends Controller
{
    // ─── LOAD PRODUCTS + CATEGORIES ──────────────────
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
                'products.stock',
                'products.price',
                'products.low_stock_threshold',
                'products.status',
                'categories.category_id',
                'categories.category_name',
                'categories.zone',
                'categories.category_type',
                'categories.is_stock_tracked'
            )
            ->orderBy('categories.zone')
            ->orderBy('categories.category_name')
            ->orderBy('products.product_name')
            ->get();

        $categories       = DB::table('categories')->orderBy('zone')->orderBy('category_name')->get();
        $categoriesByZone = $categories->groupBy('zone');
        $zones            = $categories->pluck('zone')->unique()->values();

        return view('inventory.index', compact('products', 'categoriesByZone', 'zones'));
    }

    // ─── ADD CATEGORY (dynamic — para di na kailangan i-edit ang Blade) ───
    public function storeCategory(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:100',
            'zone'          => 'required|string|max:50',
            'category_type' => 'required|in:ride,service,merchandise',
        ]);

        $exists = DB::table('categories')
            ->whereRaw('LOWER(category_name) = ?', [strtolower(trim($request->category_name))])
            ->where('zone', $request->zone)
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'That category already exists in this zone.'
            ]);
        }

        $id = DB::table('categories')->insertGetId([
            'category_name'   => trim($request->category_name),
            'zone'            => trim($request->zone),
            'category_type'   => $request->category_type,
            'is_stock_tracked'=> $request->boolean('is_stock_tracked') ? 1 : 0,
        ]);

        $category = DB::table('categories')->where('category_id', $id)->first();

        return response()->json([
            'status'   => 'success',
            'message'  => 'Category added!',
            'category' => $category
        ]);
    }

    // ─── EXPORT PRODUCTS (XLSX) ───────────────────────
    public function export()
    {
        $products = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->select(
                'products.product_name',
                'categories.zone',
                'categories.category_name',
                'categories.is_stock_tracked',
                'products.stock',
                'products.price',
                'products.low_stock_threshold',
                'products.status'
            )
            ->orderBy('categories.zone')
            ->orderBy('categories.category_name')
            ->orderBy('products.product_name')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventory');

        $sheet->setCellValue('A1', 'REKS Amusement Com Inc. — Inventory Report');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D63E63');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->setCellValue('A2', 'Generated: ' . date('F d, Y h:i A') . ' | Total Products: ' . $products->count());
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('635C72');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['Zone', 'Category', 'Product Name', 'Stock', 'Price (PHP)', 'Status'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '4', $h);
            $col++;
        }
        $sheet->getStyle('A4:F4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A4:F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1A1523');
        $sheet->getStyle('A4:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 5;
        foreach ($products as $p) {
            $isNoStock    = !$p->is_stock_tracked;
            $threshold    = intval($p->low_stock_threshold ?? 20);
            $stockDisplay = $isNoStock ? 'N/A' : intval($p->stock);
            $isLow        = !$isNoStock && intval($p->stock) <= $threshold;
            $status       = $isNoStock ? 'N/A' : ($isLow ? 'Low Stock' : 'In Stock');

            $sheet->setCellValue('A' . $row, $p->zone);
            $sheet->setCellValue('B' . $row, $p->category_name);
            $sheet->setCellValue('C' . $row, $p->product_name);
            $sheet->setCellValue('D' . $row, $stockDisplay);
            $sheet->setCellValue('E' . $row, number_format(floatval($p->price ?? 0), 2));
            $sheet->setCellValue('F' . $row, $status);

            $statusCell = 'F' . $row;
            if ($status === 'Low Stock') {
                $sheet->getStyle($statusCell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');
                $sheet->getStyle($statusCell)->getFont()->getColor()->setRGB('DC2626');
            } elseif ($status === 'In Stock') {
                $sheet->getStyle($statusCell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E4F8F4');
                $sheet->getStyle($statusCell)->getFont()->getColor()->setRGB('1FAE9E');
            }

            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':E' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2F6');
            }

            $row++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle('A4:F' . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('EEE8F0');

        foreach (['A' => 16, 'B' => 16, 'C' => 30, 'D' => 12, 'E' => 14, 'F' => 14] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        $filename = 'REKS_Inventory_' . date('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ─── IMPORT PRODUCTS (CSV) ────────────────────────
    // Expected columns: product_name, zone, category_name, stock, price
    public function import(Request $request)
    {
        if (!$request->hasFile('import_file')) {
            return response()->json(['status' => 'error', 'message' => 'No file uploaded.']);
        }

        $path   = $request->file('import_file')->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            return response()->json(['status' => 'error', 'message' => 'Unable to read the uploaded file.']);
        }

        $header = array_map(function ($h) { return strtolower(trim($h)); }, fgetcsv($handle));

        // Cache categories (zone+name -> category row) para hindi paulit-ulit mag-query sa loop
        $categories = DB::table('categories')->get()->keyBy(function ($c) {
            return strtolower($c->zone) . '|' . strtolower($c->category_name);
        });

        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowData = array_combine($header, $row);

            $name         = $rowData['product_name']  ?? null;
            $categoryName = $rowData['category_name'] ?? null;
            $zone         = $rowData['zone']           ?? null;

            if (!$name || !$categoryName || !$zone) { $skipped++; continue; }

            $key      = strtolower($zone) . '|' . strtolower($categoryName);
            $category = $categories->get($key);

            if (!$category) { $skipped++; continue; } // walang match na zone+category, skip

            $stock = $category->is_stock_tracked ? intval($rowData['stock'] ?? 0) : 0;

            DB::table('products')->insert([
                'product_name'  => $name,
                'category_id'   => $category->category_id,
                'category_name' => $category->category_name, // backup/legacy column
                'stock'         => $stock,
                'price'         => floatval($rowData['price'] ?? 0),
                'status'        => 'active',
            ]);

            $imported++;
        }

        fclose($handle);

        $message = "Imported {$imported} product(s) successfully!";
        if ($skipped > 0) {
            $message .= " {$skipped} row(s) skipped (zone/category not found).";
        }

        return response()->json(['status' => 'success', 'message' => $message]);
    }

    // ─── ADD PRODUCT ──────────────────────────────────
    public function store(Request $request)
    {
        $category = DB::table('categories')->where('category_id', $request->category_id)->first();

        if (!$category) {
            return response()->json(['status' => 'error', 'message' => 'Please select a valid category.']);
        }

        $stock = $category->is_stock_tracked
            ? (($request->stock !== '' && $request->stock !== null) ? intval($request->stock) : 0)
            : 0;

        $id = DB::table('products')->insertGetId([
            'product_name'  => $request->name,
            'category_id'   => $category->category_id,
            'category_name' => $category->category_name, // legacy/backup, laging naka-sync
            'stock'         => $stock,
            'price'         => floatval($request->price),
            'status'        => 'active',
        ]);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Product added successfully!',
            'id'       => $id,
            'category' => [
                'id'               => $category->category_id,
                'name'             => $category->category_name,
                'zone'             => $category->zone,
                'is_stock_tracked' => (bool) $category->is_stock_tracked,
            ]
        ]);
    }

    // ─── EDIT PRODUCT ─────────────────────────────────
    public function update(Request $request)
    {
        $category = DB::table('categories')->where('category_id', $request->category_id)->first();

        if (!$category) {
            return response()->json(['status' => 'error', 'message' => 'Please select a valid category.']);
        }

        $data = [
            'product_name'  => $request->name,
            'category_id'   => $category->category_id,
            'category_name' => $category->category_name,
            'price'         => floatval($request->price),
            'stock'         => $category->is_stock_tracked ? intval($request->stock) : 0,
        ];

        DB::table('products')->where('product_id', $request->id)->update($data);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Product updated!',
            'category' => [
                'id'               => $category->category_id,
                'name'             => $category->category_name,
                'zone'             => $category->zone,
                'is_stock_tracked' => (bool) $category->is_stock_tracked,
            ]
        ]);
    }

    // ─── DELETE PRODUCT ───────────────────────────────
    public function destroy(Request $request)
    {
        DB::table('products')->where('product_id', $request->id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Product deleted!']);
    }

    // ─── RESTOCK PRODUCT ──────────────────────────────
    public function restock(Request $request)
    {
        $qty = intval($request->qty);

        if ($qty <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Quantity must be greater than 0.']);
        }

        DB::table('products')->where('product_id', $request->id)->increment('stock', $qty);

        $newStock = DB::table('products')->where('product_id', $request->id)->value('stock');

        return response()->json([
            'status'    => 'success',
            'message'   => "Stock updated! Added {$qty} units.",
            'new_stock' => $newStock
        ]);
    }
}