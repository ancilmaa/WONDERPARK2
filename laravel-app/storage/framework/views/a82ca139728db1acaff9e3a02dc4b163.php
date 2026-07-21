<?php $__env->startSection('title', 'My Bookings'); ?>
<?php $__env->startSection('page-title', 'My Bookings'); ?>
<?php $__env->startSection('page-subtitle', 'Manage your reservations'); ?>
<?php $__env->startSection('body-class', 'page-uniform'); ?>

<?php $__env->startSection('content'); ?>

    <div class="u-card" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
            <h4 style="margin:0 0 4px;">My Bookings</h4>
            <p style="margin:0;">Manage upcoming &amp; past visits</p>
        </div>
        <a href="<?php echo e(route('user.booking')); ?>"
           class="u-btn ghost"
           style="width:auto;padding:9px 16px;font-size:12px;white-space:nowrap;">
            + New Booking
        </a>
    </div>

    <div class="u-card" style="margin-top:20px;padding:0;overflow:hidden;font-size:14px;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:640px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(0,0,0,0.08);">
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Package</th>
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Date</th>
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Pax</th>
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Status</th>
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr style="<?php echo e(!$loop->last ? 'border-bottom:1px solid rgba(0,0,0,0.06);' : ''); ?>">
                        <td style="padding:20px;vertical-align:middle;font-size:14px;font-weight:600;">
                            <?php echo e($booking['package']); ?>

                        </td>
                        <td style="padding:20px;vertical-align:middle;white-space:nowrap;font-size:13px;color:var(--muted);">
                            <?php echo e($booking['date']); ?>

                        </td>
                        <td style="padding:20px;vertical-align:middle;white-space:nowrap;font-size:13px;color:var(--muted);">
                            <?php echo e($booking['pax']); ?> pax
                        </td>
                        <td style="padding:20px;vertical-align:middle;">
                            <span class="tag <?php echo e($booking['status_class']); ?>"><?php echo e($booking['status_label']); ?></span>
                        </td>
                        <td style="padding:20px;vertical-align:middle;">
                            <?php if($booking['status_class'] !== 'green'): ?>
                                <div style="display:flex;flex-direction:column;gap:8px;min-width:200px;">

                                    <?php if($booking['status_class'] === 'amber'): ?>
                                        <a href="<?php echo e(route('user.bookings.review', $booking['id'])); ?>"
                                           class="u-btn"
                                           style="width:100%;padding:8px 8px;font-size:11px;font-weight:600;text-align:center;white-space:nowrap;">
                                            Review Booking
                                        </a>
                                    <?php endif; ?>

                                    <div style="display:flex;gap:8px;">
                                        <a href="<?php echo e(route('user.bookings.reschedule.edit', $booking['id'])); ?>"
                                           class="u-btn ghost"
                                           style="flex:1;padding:8px 6px;font-size:11px;font-weight:600;text-align:center;white-space:nowrap;">
                                            Reschedule
                                        </a>

                                        <form method="POST"
                                              action="<?php echo e(route('user.bookings.cancel', $booking['id'])); ?>"
                                              style="flex:1;"
                                              onsubmit="return confirm('Cancel this booking?');">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit"
                                                    class="u-btn ghost"
                                                    style="width:100%;padding:8px 6px;font-size:11px;font-weight:600;color:#e24b4a;border-color:#e24b4a;white-space:nowrap;">
                                                Cancel
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            <?php else: ?>
                                <span style="font-size:13px;color:var(--muted);">&mdash;</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" style="padding:24px 20px;text-align:center;font-size:13px;color:var(--muted);">
                            You don't have any bookings yet.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <?php if($bookings instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $bookings->hasPages()): ?>
        <div class="promo-pagination" style="margin-top:16px;justify-content:flex-start;">

            <button type="button"
                    class="pg-btn pg-prev"
                    <?php if(!$bookings->onFirstPage()): ?> onclick="window.location='<?php echo e($bookings->previousPageUrl()); ?>'" <?php else: ?> disabled <?php endif; ?>>
                &larr;
            </button>

            <?php
                $start = max(1, $bookings->currentPage() - 2);
                $end = min($bookings->lastPage(), $bookings->currentPage() + 2);
            ?>

            <?php if($start > 1): ?>
                <button type="button" class="pg-btn pg-num" onclick="window.location='<?php echo e($bookings->url(1)); ?>'">1</button>
                <?php if($start > 2): ?>
                    <span style="color:var(--muted);font-size:12px;padding:0 2px;">&hellip;</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for($page = $start; $page <= $end; $page++): ?>
                <button type="button"
                        class="pg-btn pg-num <?php echo e($page === $bookings->currentPage() ? 'active' : ''); ?>"
                        onclick="window.location='<?php echo e($bookings->url($page)); ?>'">
                    <?php echo e($page); ?>

                </button>
            <?php endfor; ?>

            <?php if($end < $bookings->lastPage()): ?>
                <?php if($end < $bookings->lastPage() - 1): ?>
                    <span style="color:var(--muted);font-size:12px;padding:0 2px;">&hellip;</span>
                <?php endif; ?>
                <button type="button" class="pg-btn pg-num" onclick="window.location='<?php echo e($bookings->url($bookings->lastPage())); ?>'">
                    <?php echo e($bookings->lastPage()); ?>

                </button>
            <?php endif; ?>

            <button type="button"
                    class="pg-btn pg-next"
                    <?php if($bookings->hasMorePages()): ?> onclick="window.location='<?php echo e($bookings->nextPageUrl()); ?>'" <?php else: ?> disabled <?php endif; ?>>
                &rarr;
            </button>

        </div>
    <?php endif; ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\WONDERPARK\WONDERPARK2\laravel-app\resources\views/user/bookings.blade.php ENDPATH**/ ?>