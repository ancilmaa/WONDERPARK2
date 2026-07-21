

<?php $__env->startSection('title', 'Reservation and Bookings'); ?>

<?php $__env->startPush('head'); ?>
    <link
        href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap"
        rel="stylesheet">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('styles'); ?>
    <style>
        :root {
            /* extra status colors used only on this page */
            --confirmed: #1FAE9E;
            --confirmed-soft: #E3F6F3;
            --pending: #C98A1F;
            --pending-soft: #FBF0DD;
            --paid: #2F6FE0;
            --paid-soft: #E7EEFC;
            --cancelled: #D63E63;
            --cancelled-soft: #FFE3EB;
        }

        .serif {
            font-family: 'Source Serif 4', serif;
        }

        /* TOOLBAR */
        .toolbar {
            background: var(--card);
            padding: 22px 26px;
            margin-bottom: 20px;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
        }

        .toolbar .eyebrow {
            font-size: .68rem;
            font-weight: 700;
            color: var(--pink-deep);
            text-transform: uppercase;
            letter-spacing: .09em;
            margin-bottom: 6px;
        }

        .toolbar h2 {
            font-family: 'Source Serif 4', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--ink);
        }

        .toolbar p.sub {
            font-size: .78rem;
            color: var(--muted);
            margin-top: 4px;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* TABLE BOX */
        .table-box {
            background: var(--card);
            padding: 28px 26px;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .sample-note {
            font-size: .75rem;
            color: var(--pink-deep);
            background: var(--pink-pale);
            border: 1px solid var(--pink-light);
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 18px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
            font-size: 13px;
        }

        th,
        td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid var(--line);
        }

        thead tr th {
            background: var(--ink);
            color: #fff;
            font-weight: 600;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 12px 14px;
        }

        thead tr th:first-child {
            border-top-left-radius: 10px;
        }

        thead tr th:last-child {
            border-top-right-radius: 10px;
        }

        tbody tr:hover {
            background: var(--pink-pale);
        }

        td {
            color: var(--ink-soft);
        }

        td:first-child {
            color: var(--ink);
            font-weight: 600;
        }

        td.email-cell {
            color: var(--ink-soft);
            font-size: 12px;
        }

        /* STATUS BADGES */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: capitalize;
            letter-spacing: .02em;
        }

        .badge-confirmed {
            background: var(--confirmed-soft);
            color: var(--confirmed);
        }

        .badge-pending {
            background: var(--pending-soft);
            color: var(--pending);
        }

        .badge-paid {
            background: var(--paid-soft);
            color: var(--paid);
        }

        .badge-cancelled {
            background: var(--cancelled-soft);
            color: var(--cancelled);
        }

        /* STATUS DROPDOWN (inline-editable, styled like a badge) */
        .status-form {
            display: inline-block;
        }

        .status-select {
            appearance: none;
            -webkit-appearance: none;
            padding: 5px 26px 5px 12px;
            border-radius: 999px;
            border: 1px solid transparent;
            font-size: 11px;
            font-weight: 700;
            text-transform: capitalize;
            letter-spacing: .02em;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%23635C72' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            transition: .15s;
        }

        .status-select:focus {
            outline: none;
            box-shadow: 0 0 0 3px var(--pink-light);
        }

        .status-select[data-status="pending"] {
            background-color: var(--pending-soft);
            color: var(--pending);
        }

        .status-select[data-status="confirmed"] {
            background-color: var(--confirmed-soft);
            color: var(--confirmed);
        }

        .status-select[data-status="paid"] {
            background-color: var(--paid-soft);
            color: var(--paid);
        }

        .status-select[data-status="cancelled"] {
            background-color: var(--cancelled-soft);
            color: var(--cancelled);
        }

        .action-buttons {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-icon-edit,
        .btn-icon-delete {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--line-strong);
            background: #fff;
            color: var(--muted);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            transition: .15s;
        }

        .btn-icon-edit:hover {
            background: var(--paid-soft);
            border-color: var(--paid);
            color: var(--paid);
        }

        .delete-form {
            display: inline-block;
        }

        .btn-icon-delete:hover {
            background: var(--cancelled-soft);
            border-color: var(--cancelled);
            color: var(--cancelled);
        }

        /* PAGINATION (client-side JS) */
        .pagination {
            display: none;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 6px;
            padding-top: 18px;
            margin-top: 4px;
            border-top: 1px solid var(--line);
        }

        .pagination-info {
            font-size: 11.5px;
            color: var(--muted);
            margin-right: 6px;
        }

        .pagination button {
            min-width: 30px;
            padding: 7px 10px;
            border: 1px solid var(--line-strong);
            background: #fff;
            color: var(--ink-soft);
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
        }

        .pagination button:hover:not(:disabled):not(.active) {
            background: var(--pink-pale);
            border-color: var(--pink);
            color: var(--pink-deep);
        }

        .pagination button.active {
            background: var(--pink-deep);
            border-color: var(--pink-deep);
            color: #fff;
        }

        .pagination button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        #noMatchRow td {
            padding: 26px !important;
            color: var(--muted) !important;
            background: var(--card) !important;
        }

        /* BUTTONS */
        .btn-primary {
            padding: 11px 20px;
            background: var(--pink-deep);
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            transition: background .15s ease, box-shadow .15s ease;
        }

        .btn-primary:hover {
            background: var(--pink-dark);
            box-shadow: 0 0 0 3px var(--pink-light);
        }

        .btn-ghost {
            padding: 11px 18px;
            background: #fff;
            color: var(--ink);
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
            transition: background .15s ease, border-color .15s ease;
        }

        .btn-ghost:hover {
            background: var(--bg);
            border-color: var(--pink);
        }

        /* ===== MODALS (New / Edit Reservation) ===== */
        #reservationOverlay,
        #editReservationOverlay,
        #confirmOverlay,
        #receiptOverlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            align-items: center;
            justify-content: center;
            background: rgba(26, 21, 35, .45);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            padding: 20px;
            animation: overlayFade .2s ease;
        }

        #reservationOverlay.active,
        #editReservationOverlay.active,
        #confirmOverlay.active,
        #receiptOverlay.active {
            display: flex;
        }

        .modal-card {
            background: #fff;
            border-radius: 18px;
            width: 100%;
            max-width: 520px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            box-shadow: var(--shadow-md);
            animation: cardPop .25s cubic-bezier(.34, 1.56, .64, 1);
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 22px 24px 18px;
            border-bottom: 1px solid var(--line);
        }

        .modal-head .eyebrow {
            font-size: .66rem;
            font-weight: 700;
            color: var(--pink-deep);
            text-transform: uppercase;
            letter-spacing: .09em;
            margin-bottom: 4px;
        }

        .modal-head h3 {
            font-family: 'Source Serif 4', serif;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--ink);
        }

        .modal-close {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            border: none;
            background: var(--bg);
            color: var(--ink-soft);
            font-size: 14px;
            cursor: pointer;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: .15s;
        }

        .modal-close:hover {
            background: var(--pink-light);
            color: var(--pink-deep);
        }

        .modal-body {
            padding: 22px 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--ink-soft);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 12px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            font-size: 13.5px;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            background: var(--bg);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--pink);
            background: #fff;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 70px;
        }

        .form-hint {
            font-size: 11px;
            color: var(--muted);
        }

        .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 18px 24px 24px;
        }

        @keyframes overlayFade {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes cardPop {
            from {
                opacity: 0;
                transform: scale(.94) translateY(6px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* ===== CONFIRM DELETE MODAL ===== */
        .confirm-card {
            background: #fff;
            border-radius: 18px;
            width: 100%;
            max-width: 380px;
            padding: 30px 28px 24px;
            text-align: center;
            box-shadow: var(--shadow-md);
            animation: cardPop .25s cubic-bezier(.34, 1.56, .64, 1);
        }

        .confirm-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            margin: 0 auto 18px;
            background: var(--cancelled-soft);
            color: var(--cancelled);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .confirm-card h3 {
            font-family: 'Source Serif 4', serif;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .confirm-card p {
            font-size: 13.5px;
            color: var(--ink-soft);
            line-height: 1.5;
            margin-bottom: 24px;
        }

        .confirm-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .confirm-actions button {
            flex: 1;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: .15s;
            border: none;
        }

        .btn-confirm-cancel {
            background: var(--pink-pale);
            color: var(--pink-deep);
        }

        .btn-confirm-cancel:hover {
            background: var(--pink-light);
        }

        .btn-confirm-delete {
            background: var(--cancelled);
            color: #fff;
        }

        .btn-confirm-delete:hover {
            background: var(--pink-deep);
            box-shadow: 0 0 0 3px var(--pink-light);
        }

        @media(max-width:420px) {
            .confirm-actions {
                flex-direction: column-reverse;
            }
        }

        /* ===== TOAST ===== */
        #toastStack {
            position: fixed;
            top: 22px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10001;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 100%;
            max-width: 420px;
            padding: 0 16px;
            pointer-events: none;
        }

        .toast {
            pointer-events: auto;
            width: 100%;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fff;
            color: var(--ink);
            padding: 14px 16px;
            border-radius: 14px;
            border-left: 4px solid var(--ink);
            box-shadow: var(--shadow-md);
            font-size: 13px;
            font-weight: 500;
            line-height: 1.4;
            opacity: 0;
            transform: translateY(-16px) scale(.97);
            animation: toastIn .35s cubic-bezier(.34, 1.56, .64, 1) forwards;
            position: relative;
            overflow: hidden;
        }

        .toast.hide {
            animation: toastOut .28s ease forwards;
        }

        .toast .toast-icon {
            flex-shrink: 0;
            width: 26px;
            height: 26px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: #fff;
            background: var(--ink-soft);
        }

        .toast.toast-success {
            border-left-color: var(--confirmed);
        }

        .toast.toast-success .toast-icon {
            background: var(--confirmed);
        }

        .toast.toast-error {
            border-left-color: var(--cancelled);
        }

        .toast.toast-error .toast-icon {
            background: var(--cancelled);
        }

        .toast .toast-body {
            flex: 1;
            min-width: 0;
            padding-top: 2px;
        }

        .toast .toast-title {
            font-weight: 700;
            font-size: 12.5px;
            margin-bottom: 2px;
            color: var(--ink);
        }

        .toast .toast-msg {
            color: var(--ink-soft);
            font-size: 12.5px;
            word-break: break-word;
        }

        .toast .toast-close {
            flex-shrink: 0;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            font-size: 13px;
            padding: 2px;
        }

        .toast .toast-close:hover {
            color: var(--ink);
        }

        .toast .toast-bar {
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            background: currentColor;
            opacity: .35;
            animation: toastShrink 3s linear forwards;
        }

        @keyframes toastIn {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes toastOut {
            to {
                opacity: 0;
                transform: translateY(-12px) scale(.96);
            }
        }

        @keyframes toastShrink {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        @media(max-width:520px) {
            #toastStack {
                top: 14px;
                max-width: calc(100% - 24px);
                padding: 0;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .modal-foot {
                flex-direction: column-reverse;
            }

            .modal-foot button {
                width: 100%;
            }
        }

        /* ===== CARD VIEW on very small screens ===== */
        @media(max-width:480px) {

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead {
                display: none;
            }

            tbody tr {
                border: 1px solid var(--line-strong);
                border-radius: 12px;
                margin-bottom: 12px;
                padding: 12px 14px;
                background: var(--card);
            }

            tbody tr:hover {
                background: var(--card);
            }

            td {
                border: none;
                padding: 5px 0;
                font-size: 13px;
                display: flex;
                align-items: flex-start;
                gap: 8px;
            }

            td::before {
                content: attr(data-label);
                font-weight: 700;
                color: var(--muted);
                min-width: 80px;
                font-size: 10.5px;
                text-transform: uppercase;
                letter-spacing: .04em;
                padding-top: 2px;
            }

            table {
                min-width: unset;
            }
        }

        @media(max-width:900px) {
            .toolbar h2 {
                font-size: 1.05rem;
            }

            table {
                min-width: 680px;
                font-size: 12px;
            }

            th,
            td {
                padding: 9px 10px;
            }
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php
        $dummyRows = [
            ['customer' => 'Juan Dela Cruz', 'email' => 'juan.delacruz@example.com', 'date' => 'Jul 02, 2026', 'time' => '10:00 AM', 'package' => 'Whole Day Pass', 'pax' => 4, 'status' => 'confirmed'],
            ['customer' => 'Maria Santos', 'email' => 'maria.santos@example.com', 'date' => 'Jul 03, 2026', 'time' => '1:00 PM', 'package' => 'Birthday Package', 'pax' => 12, 'status' => 'pending'],
            ['customer' => 'Ana Reyes', 'email' => 'ana.reyes@example.com', 'date' => 'Jul 05, 2026', 'time' => '9:00 AM', 'package' => 'Half Day Pass', 'pax' => 2, 'status' => 'confirmed'],
            ['customer' => 'Mark Villanueva', 'email' => 'mark.villanueva@example.com', 'date' => 'Jul 06, 2026', 'time' => '2:30 PM', 'package' => 'Group Package', 'pax' => 20, 'status' => 'cancelled'],
        ];
        $usingDummyData = !isset($bookings) || $bookings->count() === 0;
        $packageOptions = $packages ?? ['Whole Day Pass', 'Half Day Pass', 'Birthday Package', 'Group Package'];
    ?>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <div>
            <div class="eyebrow">Lipa Branch &middot; Customer Management</div>
            <h2>Reservation and Bookings</h2>
            <p class="sub">Upcoming reservations and schedules</p>
        </div>
        <div class="toolbar-actions">
            <button type="button" class="btn-primary" id="openReservationModalBtn">
                <i class="fa-solid fa-plus"></i> New Reservation
            </button>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-box">

        <?php if($usingDummyData): ?>
            <div class="sample-note"><i class="fa-solid fa-circle-info"></i> Showing sample data &mdash; no
                reservations recorded yet.</div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Package</th>
                    <th>Pax</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($usingDummyData): ?>
                    <?php $__currentLoopData = $dummyRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td data-label="Customer"><?php echo e($row['customer']); ?></td>
                            <td data-label="Email" class="email-cell"><?php echo e($row['email']); ?></td>
                            <td data-label="Date"><?php echo e($row['date']); ?></td>
                            <td data-label="Time"><?php echo e($row['time']); ?></td>
                           <td data-label="Package"><?php echo e($row['package']); ?></td>
                            <td data-label="Pax"><?php echo e($row['pax']); ?></td>
                            <td data-label="Payment">&mdash;</td>
                            <td data-label="Status">
                                <span class="badge badge-<?php echo e($row['status']); ?>"><?php echo e(ucfirst($row['status'])); ?></span>
                            </td>
                            <td data-label="Actions">&mdash;</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                          <td data-label="Customer">
    <?php echo e($booking->display_customer->fullname ?? ($booking->customer_name ?? 'Walk-in')); ?>

</td>
<td data-label="Email" class="email-cell">
    <?php echo e($booking->display_customer->email ?? '—'); ?>

</td>
<td data-label="Date"><?php echo e($booking->display_date?->format('M d, Y')); ?></td>
<td data-label="Time"><?php echo e($booking->display_time); ?></td>
<td data-label="Package"><?php echo e($booking->package); ?></td>
<td data-label="Pax"><?php echo e($booking->display_pax); ?></td>
<td data-label="Payment">
    <?php if($booking->payment_method === 'qrph'): ?>
        <span class="badge" style="background:var(--paid-soft);color:var(--paid);">QR Ph</span>
    <?php elseif($booking->payment_method === 'cash'): ?>
        <span class="badge" style="background:var(--pending-soft);color:var(--pending);">Cash</span>
    <?php else: ?>
        <span style="color:var(--muted);font-size:12px;">&mdash;</span>
    <?php endif; ?>
</td>
                            <td data-label="Status">
                                <form action="<?php echo e(route('reservations.update', $booking)); ?>" method="POST"
                                    class="status-form">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <select name="status" class="status-select" data-status="<?php echo e($booking->status); ?>"
                                        onchange="this.dataset.status=this.value; this.form.submit();">
                                        <option value="pending" <?php echo e($booking->status === 'pending' ? 'selected' : ''); ?>>
                                            Pending</option>
                                        <option value="confirmed"
                                            <?php echo e($booking->status === 'confirmed' ? 'selected' : ''); ?>>Confirmed</option>
                                        <option value="paid" <?php echo e($booking->status === 'paid' ? 'selected' : ''); ?>>Paid
                                        </option>
                                        <option value="cancelled"
                                            <?php echo e($booking->status === 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                            <td data-label="Actions">
                                <div class="action-buttons">
                                    <button type="button" class="btn-icon-edit" title="Edit reservation"
                                        onclick="openEditModal({
                                            id: '<?php echo e($booking->id); ?>',
                                           customer_name: <?php echo \Illuminate\Support\Js::from($booking->display_customer->fullname ?? $booking->customer_name ?? '')->toHtml() ?>,
                                            customer_contact: <?php echo \Illuminate\Support\Js::from($booking->customer_contact ?? '')->toHtml() ?>,
                                            package: <?php echo \Illuminate\Support\Js::from($booking->package)->toHtml() ?>,
                                           pax: <?php echo e($booking->display_pax); ?>,
                                        reservation_date: '<?php echo e($booking->display_date?->format('Y-m-d')); ?>',
                                        reservation_time: '<?php echo e($booking->display_time); ?>',
                                            notes: <?php echo \Illuminate\Support\Js::from($booking->notes ?? '')->toHtml() ?>
                                        })">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                        <?php if($booking->payment_method === 'qrph' && $booking->receipt_path): ?>
                                                    <button type="button" class="btn-icon-edit" title="View receipt"
                                                        onclick="openReceiptModal('<?php echo e(asset('storage/' . $booking->receipt_path)); ?>')">
                                                        <i class="fa-solid fa-receipt"></i>
                                                    </button>
                                                <?php endif; ?>
                                    <form action="<?php echo e(route('reservations.destroy', $booking)); ?>" method="POST"
                                        class="delete-form">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn-icon-delete" title="Delete reservation">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination no-print" id="pagination"></div>
    </div>

    <!-- NEW RESERVATION MODAL -->
    <div id="reservationOverlay" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="reservationModalTitle">
            <form id="newReservationForm" method="POST" action="<?php echo e(route('reservations.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-head">
                    <div>
                        <div class="eyebrow">Lipa Branch</div>
                        <h3 id="reservationModalTitle">New Reservation</h3>
                    </div>
                    <button type="button" class="modal-close" id="closeReservationModal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="new_customer_name">Customer name</label>
                            <input type="text" name="customer_name" id="new_customer_name" required>
                        </div>
                        <div class="form-group full">
                            <label for="new_customer_contact">Contact number</label>
                            <input type="text" name="customer_contact" id="new_customer_contact">
                        </div>
                        <div class="form-group">
                            <label for="new_package">Package</label>
                            <select name="package" id="new_package" required>
                                <?php $__currentLoopData = $packageOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($pkg); ?>"><?php echo e($pkg); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="new_pax">Number of pax</label>
                            <input type="number" name="pax" id="new_pax" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="new_reservation_date">Date</label>
                            <input type="date" name="reservation_date" id="new_reservation_date" required>
                        </div>
                        <div class="form-group">
                            <label for="new_reservation_time">Time</label>
                            <input type="time" name="reservation_time" id="new_reservation_time" required>
                        </div>
                        <div class="form-group full">
                            <label for="new_notes">Notes (optional)</label>
                            <textarea name="notes" id="new_notes"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-ghost" id="cancelReservationModal">Cancel</button>
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Save Reservation</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT RESERVATION MODAL -->
    <div id="editReservationOverlay" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="editReservationModalTitle">
            <form id="editReservationForm" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-head">
                    <div>
                        <div class="eyebrow">Lipa Branch</div>
                        <h3 id="editReservationModalTitle">Edit Reservation</h3>
                    </div>
                    <button type="button" class="modal-close" id="closeEditReservationModal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="edit_customer_name">Customer name</label>
                            <input type="text" name="customer_name" id="edit_customer_name" required>
                        </div>
                        <div class="form-group full">
                            <label for="edit_customer_contact">Contact number</label>
                            <input type="text" name="customer_contact" id="edit_customer_contact">
                        </div>
                        <div class="form-group">
                            <label for="edit_package">Package</label>
                            <select name="package" id="edit_package" required>
                                <?php $__currentLoopData = $packageOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($pkg); ?>"><?php echo e($pkg); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_pax">Number of pax</label>
                            <input type="number" name="pax" id="edit_pax" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_reservation_date">Date</label>
                            <input type="date" name="reservation_date" id="edit_reservation_date" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_reservation_time">Time</label>
                            <input type="time" name="reservation_time" id="edit_reservation_time" required>
                        </div>
                        <div class="form-group full">
                            <label for="edit_notes">Notes (optional)</label>
                            <textarea name="notes" id="edit_notes"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-ghost" id="cancelEditReservationModal">Cancel</button>
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- CONFIRM DELETE MODAL -->
    <div id="confirmOverlay" aria-hidden="true">
        <div class="confirm-card" role="alertdialog" aria-modal="true" aria-labelledby="confirmTitle"
            aria-describedby="confirmMessage">
            <div class="confirm-icon"><i class="fa-solid fa-trash"></i></div>
            <h3 id="confirmTitle">Delete this reservation?</h3>
            <p id="confirmMessage">Are you sure you want to delete this reservation? This action cannot be undone.</p>
            <div class="confirm-actions">
                <button type="button" class="btn-confirm-cancel" id="confirmCancelBtn">Cancel</button>
                <button type="button" class="btn-confirm-delete" id="confirmOkBtn">Yes, Delete</button>
            </div>
        </div>
    </div>
<!-- VIEW RECEIPT MODAL -->
    <div id="receiptOverlay" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="receiptModalTitle" style="max-width:480px;">
            <div class="modal-head">
                <div>
                    <div class="eyebrow">Payment Proof</div>
                    <h3 id="receiptModalTitle">Payment Receipt</h3>
                </div>
                <button type="button" class="modal-close" id="closeReceiptModal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body" style="text-align:center;">
                <img id="receiptImage" src="" alt="Payment receipt" style="max-width:100%;border-radius:12px;">
            </div>
        </div>
    </div>
    <!-- TOAST STACK -->
    <div id="toastStack" aria-live="polite"></div>

    <?php if(session('success')): ?>
        <span id="flash-success" data-msg="<?php echo e(session('success')); ?>" style="display:none;"></span>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <span id="flash-error" data-msg="<?php echo e(session('error')); ?>" style="display:none;"></span>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <span id="flash-error" data-msg="<?php echo e($errors->first()); ?>" style="display:none;"></span>
    <?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        // ── New Reservation modal ──
        const reservationOverlay = document.getElementById('reservationOverlay');
        const openReservationModalBtn = document.getElementById('openReservationModalBtn');
        const closeReservationModalBtn = document.getElementById('closeReservationModal');
        const cancelReservationModalBtn = document.getElementById('cancelReservationModal');

        function openReservationModal() {
            reservationOverlay.classList.add('active');
            reservationOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeReservationModal() {
            reservationOverlay.classList.remove('active');
            reservationOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        openReservationModalBtn?.addEventListener('click', openReservationModal);
        closeReservationModalBtn?.addEventListener('click', closeReservationModal);
        cancelReservationModalBtn?.addEventListener('click', closeReservationModal);
        reservationOverlay?.addEventListener('click', (e) => {
            if (e.target === reservationOverlay) closeReservationModal();
        });

        // ── Edit Reservation modal ──
        const editReservationOverlay = document.getElementById('editReservationOverlay');
        const editReservationForm = document.getElementById('editReservationForm');
        const closeEditReservationBtn = document.getElementById('closeEditReservationModal');
        const cancelEditReservationBtn = document.getElementById('cancelEditReservationModal');

        function openEditModal(data) {
            editReservationForm.action = `/reservations/${data.id}`;

            document.getElementById('edit_customer_name').value = data.customer_name || '';
            document.getElementById('edit_customer_contact').value = data.customer_contact || '';
            document.getElementById('edit_package').value = data.package || '';
            document.getElementById('edit_pax').value = data.pax || '';
            document.getElementById('edit_reservation_date').value = data.reservation_date || '';
            document.getElementById('edit_reservation_time').value = data.reservation_time || '';
            document.getElementById('edit_notes').value = data.notes || '';

            editReservationOverlay.classList.add('active');
            editReservationOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            editReservationOverlay.classList.remove('active');
            editReservationOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        closeEditReservationBtn?.addEventListener('click', closeEditModal);
        cancelEditReservationBtn?.addEventListener('click', closeEditModal);
        editReservationOverlay?.addEventListener('click', (e) => {
            if (e.target === editReservationOverlay) closeEditModal();
        });

        // If validation failed server-side and the page reloaded with old input,
        // reopen whichever modal was being submitted.
        <?php if($errors->any()): ?>
            <?php if(old('_method') === 'PUT'): ?>
                // an edit was in flight — nothing to prefill without the original booking id,
                // so just leave the page as-is; the flash error toast below still shows.
            <?php else: ?>
                openReservationModal();
            <?php endif; ?>
        <?php endif; ?>
        // ── View Receipt modal ──
        const receiptOverlay = document.getElementById('receiptOverlay');
        const receiptImage = document.getElementById('receiptImage');
        const closeReceiptModalBtn = document.getElementById('closeReceiptModal');

        function openReceiptModal(url) {
            receiptImage.src = url;
            receiptOverlay.classList.add('active');
            receiptOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeReceiptModal() {
            receiptOverlay.classList.remove('active');
            receiptOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            receiptImage.src = '';
        }

        closeReceiptModalBtn?.addEventListener('click', closeReceiptModal);
        receiptOverlay?.addEventListener('click', (e) => {
            if (e.target === receiptOverlay) closeReceiptModal();
        });

        // ── Custom delete confirmation modal ──
        const confirmOverlay = document.getElementById('confirmOverlay');
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');
        const confirmOkBtn = document.getElementById('confirmOkBtn');
        let pendingDeleteForm = null;

        function openConfirmModal(form) {
            pendingDeleteForm = form;
            confirmOverlay.classList.add('active');
            confirmOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeConfirmModal() {
            confirmOverlay.classList.remove('active');
            confirmOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            pendingDeleteForm = null;
        }

        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                openConfirmModal(form);
            });
        });

        confirmCancelBtn?.addEventListener('click', closeConfirmModal);
        confirmOverlay?.addEventListener('click', (e) => {
            if (e.target === confirmOverlay) closeConfirmModal();
        });
        confirmOkBtn?.addEventListener('click', () => {
            if (pendingDeleteForm) {
                confirmOkBtn.disabled = true;
                confirmOkBtn.textContent = 'Deleting...';
                pendingDeleteForm.submit();
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (confirmOverlay.classList.contains('active')) closeConfirmModal();
                if (editReservationOverlay.classList.contains('active')) closeEditModal();
                if (reservationOverlay.classList.contains('active')) closeReservationModal();
                if (receiptOverlay.classList.contains('active')) closeReceiptModal();
            }
        });

        // ── Toast notifications ──
        const toastStack = document.getElementById('toastStack');

        const TOAST_ICONS = {
            success: { icon: 'fa-solid fa-circle-check', title: 'Success' },
            error: { icon: 'fa-solid fa-circle-exclamation', title: 'Error' }
        };

        function showToast(msg, type = "success", opts = {}) {
            const cfg = TOAST_ICONS[type] || TOAST_ICONS.success;
            const title = opts.title || cfg.title;
            const duration = opts.duration ?? 3500;

            const el = document.createElement('div');
            el.className = `toast toast-${type}`;
            el.innerHTML = `
                <div class="toast-icon"><i class="${cfg.icon}"></i></div>
                <div class="toast-body">
                    <div class="toast-title">${title}</div>
                    <div class="toast-msg"></div>
                </div>
                <button type="button" class="toast-close" aria-label="Dismiss"><i class="fa-solid fa-xmark"></i></button>
                <div class="toast-bar" style="animation-duration:${duration}ms;"></div>
            `;
            el.querySelector('.toast-msg').textContent = msg;

            function dismiss() {
                if (!el.isConnected) return;
                el.classList.add('hide');
                setTimeout(() => el.remove(), 280);
            }

            el.querySelector('.toast-close').addEventListener('click', dismiss);
            toastStack.appendChild(el);

            if (duration > 0) setTimeout(dismiss, duration);
        }

        const successMsg = document.getElementById('flash-success')?.dataset.msg;
        const errorMsg = document.getElementById('flash-error')?.dataset.msg;

        if (successMsg) showToast(successMsg, "success");
        if (errorMsg) showToast(errorMsg, "error");

        // ===== CLIENT-SIDE PAGINATION (10 per page) =====
        (function() {
            const paginationEl = document.getElementById('pagination');
            const tbody = document.querySelector('.table-box table tbody');
            if (!paginationEl || !tbody) return;

            const PAGE_SIZE = 10;
            let currentPage = 1;

            const rows = Array.from(tbody.querySelectorAll('tr'));
            if (!rows.length) return;

            function renderPagination(totalPages) {
                if (totalPages <= 1) {
                    paginationEl.style.display = 'none';
                    paginationEl.innerHTML = '';
                    return;
                }
                paginationEl.style.display = 'flex';
                let html = `<span class="pagination-info">Page ${currentPage} of ${totalPages}</span>`;
                html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}">&laquo; Prev</button>`;
                for (let p = 1; p <= totalPages; p++) {
                    html += `<button class="${p === currentPage ? 'active' : ''}" data-page="${p}">${p}</button>`;
                }
                html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}">Next &raquo;</button>`;
                paginationEl.innerHTML = html;
                paginationEl.querySelectorAll('button[data-page]').forEach(b => {
                    b.addEventListener('click', () => {
                        currentPage = parseInt(b.dataset.page, 10);
                        render();
                    });
                });
            }

            function render() {
                const totalPages = Math.max(1, Math.ceil(rows.length / PAGE_SIZE));
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);
                const start = (currentPage - 1) * PAGE_SIZE;
                const end = start + PAGE_SIZE;
                rows.forEach((row, i) => {
                    row.style.display = (i >= start && i < end) ? '' : 'none';
                });
                renderPagination(totalPages);
            }

            render();
        })();
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\REKS\REKS\laravel-app\resources\views/customer/reservations.blade.php ENDPATH**/ ?>