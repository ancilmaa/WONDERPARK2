<?php $__env->startSection('title', 'Booking'); ?>
<?php $__env->startSection('page-title', 'Booking'); ?>
<?php $__env->startSection('page-subtitle', 'Pick a date and package'); ?>
<?php $__env->startSection('body-class', 'page-uniform'); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        @media (max-width: 768px) {
    #bookingForm > .booking-submit-btn {
        width: 100%;
        margin-top: 24px;
    }
}
        /* loading state for the booking submit button */
        .booking-submit-btn.is-loading {
            opacity: .75;
            cursor: not-allowed;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-spinner {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            display: inline-block;
            animation: btnSpin .7s linear infinite;
        }

        @keyframes btnSpin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ---------- overall page rhythm ---------- */
       .booking-split {
    gap: 32px !important;
    align-items: flex-start; /* bago — para di na mag stretch */
}
.booking-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .55);
    z-index: 200;
    align-items: center;
    justify-content: center;
}
.booking-modal-overlay.active {
    display: flex;
}
.booking-modal {
    background: #fff;
    border-radius: 16px;
    padding: 30px 26px;
    width: 90%;
    max-width: 380px;
    text-align: center;
    box-shadow: 0 20px 50px rgba(0,0,0,.25);
    animation: bookingModalPop .18s ease;
}
@keyframes bookingModalPop {
    from { transform: scale(.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.booking-modal-icon {
    width: 52px;
    height: 52px;
    margin: 0 auto 14px;
    border-radius: 50%;
    background: var(--pink-pale, #ffe6ee);
    color: var(--pink-deep, #b82850);
    display: flex;
    align-items: center;
    justify-content: center;
}
.booking-modal h3 {
    font-size: 1.05rem;
    font-weight: 700;
    margin-bottom: 6px;
    color: #1a1523;
}
.booking-modal-sub {
    font-size: .85rem;
    color: #635c72;
    margin-bottom: 18px;
}
.booking-modal-summary {
    background: #f8f6f9;
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 20px;
    text-align: left;
}
.booking-modal-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    font-size: .82rem;
    padding: 5px 0;
}
.booking-modal-row span {
    color: #9c94ab;
}
.booking-modal-row b {
    color: #1a1523;
    text-align: right;
}
.booking-modal-actions {
    display: flex;
    gap: 10px;
}
.booking-modal-actions button {
    flex: 1;
    padding: 11px 16px;
    border-radius: 10px;
    font-weight: 600;
    font-size: .86rem;
    border: none;
    cursor: pointer;
}
.btn-cancel {
    background: #f1eef2;
    color: #635c72;
}
.btn-cancel:hover {
    background: #e5e0e8;
}
.btn-confirm {
    background: var(--pink-deep, #b82850);
    color: #fff;
}
.btn-confirm:hover {
    background: var(--pink-dark, #d63e63);
}

/* pagination controls */
.promo-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding-top: 4px;
}
.promo-pagination[hidden] { display: none; }
.promo-pagination button {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1.5px solid var(--border, #eee);
    background: #fff;
    color: var(--ink-soft);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background .15s var(--ease), color .15s var(--ease);
}
.promo-pagination button:hover:not(:disabled) {
    background: var(--pink-deep);
    color: #fff;
    border-color: var(--pink-deep);
}
.promo-pagination button:disabled { opacity: .35; cursor: not-allowed; }
.promo-pagination .promo-page-info {
    font-size: 12px;
    font-weight: 700;
    color: var(--muted);
    min-width: 46px;
    text-align: center;
}
        .booking-col-pkg {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .service-packages {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .service-packages[hidden] { display: none; }

        /* ---------- category filter tabs (All / Solo / Bundle / Packages) ---------- */
        /* ---------- category filter tabs (All / Solo / Bundle / Packages) ---------- */
.cat-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 5px;
    background: var(--bg);
    border: 1px solid var(--line, #eee);
    border-radius: 14px;
}
.cat-filter button {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 16px;
    border-radius: 10px;
    border: 1.5px solid transparent;
    background: #fd81d8ee;
    color: var(--ink-deep);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .01em;
    cursor: pointer;
    transition: background .15s var(--ease), color .15s var(--ease),
                box-shadow .15s var(--ease), transform .1s var(--ease),
                border-color .15s var(--ease);
}
.cat-filter button svg {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    opacity: .7;
    transition: opacity .15s var(--ease);
}
.cat-filter button .cat-count {
    font-size: 10px;
    font-weight: 800;
    color: var(--muted);
    background: var(--bg);
    border-radius: 999px;
    padding: 1px 6px;
    line-height: 1.4;
}
.cat-filter button:hover {
    background: var(--pink-pale, #ffe6ee);
    color: var(--pink-dark);
    border-color: var(--pink-deep);
    box-shadow: var(--shadow-card);
    transform: translateY(-1px);
}
.cat-filter button:hover svg { opacity: 1; }
.cat-filter button:hover .cat-count {
    background: #fff;
    color: var(--pink-dark);
}
.cat-filter button.active {
    background: var(--pink-deep);
    color: #fff;
    border-color: var(--red-deep);
    box-shadow: 0 6px 16px -8px rgba(184,40,80,.55);
    transform: translateY(-1px);
}
.cat-filter button.active svg { opacity: 1; }
.cat-filter button.active .cat-count {
    background: rgba(255,255,255,.22);
    color: #fff;
}

[data-theme="dark"] .cat-filter {
    background: var(--d-surface-soft);
    border-color: var(--d-border);
}
[data-theme="dark"] .cat-filter button {
    background: var(--d-surface);
    color: var(--d-text-soft);
    border-color: transparent;
}
[data-theme="dark"] .cat-filter button .cat-count {
    background: var(--d-surface-soft);
    color: var(--d-muted);
}
[data-theme="dark"] .cat-filter button:hover {
    background: rgba(184,40,80,.16);
    color: #fff;
    border-color: var(--pink-deep);
}
[data-theme="dark"] .cat-filter button:hover .cat-count {
    background: rgba(255,255,255,.14);
    color: #fff;
}
[data-theme="dark"] .cat-filter button.active {
    background: var(--pink-deep);
    color: #fff;
}

        .promo-empty-filter {
            font-size: 12.5px;
            color: var(--muted);
            text-align: center;
            padding: 28px 0;
            margin: 0;
        }

        /* give the promo grid + pkgSummary/service-switch breathing room
           without relying on scattered margin-top values */
        #pkgSummary { margin: 0; }
        #serviceSwitch { margin: 0; }
        .promo-grid { margin: 0 !important; }

        /* calendar column: consistent gap instead of ad hoc margins on
           the summary bar / hidden inputs / submit button */
        #calSelectedBar { margin: 0; }
        #bookingSubmitBtn { margin: 0; }

      @media (max-width: 768px) {
    #bookingForm > .booking-submit-btn {
        width: 100%;
        margin-top: 24px;
    }
    .booking-col-cal {
        position: static;
        top: auto;
    }
}
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

    <form method="POST" action="<?php echo e(route('user.booking.store')); ?>" id="bookingForm">
        <?php echo csrf_field(); ?>

        <div class="booking-split">

            
            <div class="booking-col booking-col-cal">
                <div class="cal-embed" id="calEmbed">
                    <div class="cal-embed-inner">
                        <div class="cal-month">
                            <div class="cal-month-head">
                                <button type="button" id="calPrev" aria-label="Previous month">&lsaquo;</button>
                                <span id="calMonthLabel"></span>
                                <button type="button" id="calNext" aria-label="Next month">&rsaquo;</button>
                            </div>
                            <div class="cal-weekdays">
                                <span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
                            </div>
                            <div class="cal-days" id="calDays"></div>
                        </div>

                        <div class="cal-times">
                            <div class="cal-times-head">
                                <span id="calSelectedDayLabel">Pick a date</span>
                                <div class="cal-fmt-toggle">
                                    <button type="button" data-fmt="12" class="active">12h</button>
                                    <button type="button" data-fmt="24">24h</button>
                                </div>
                            </div>
                            <div class="cal-slots" id="calSlots">
                                <p class="cal-slots-empty">Select a date first</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="calSelectedBar" class="promo-selection-summary empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;flex-shrink:0;">
                        <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                        <path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path>
                    </svg>
                    <span id="dateTimeLabel">No date &amp; time selected yet</span>
                </div>
                <input type="hidden" name="visit_date" id="visitDateInput" required>
                <input type="hidden" name="visit_time" id="visitTimeInput" required>
                <button type="submit" class="u-btn booking-submit-btn" id="bookingSubmitBtn">+ Booking</button>
                 <span id="bookingBtnAnchor" style="display:none;"></span>
            </div>

            
            <div class="booking-col booking-col-pkg">
                <div class="service-switch" id="serviceSwitch">
                    <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $svcCode => $svc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button" class="service-tab <?php echo e($loop->first ? 'active' : ''); ?>" data-service="<?php echo e($svcCode); ?>">
                            <img src="<?php echo e(asset('images/'.$svc['image'])); ?>" alt="<?php echo e($svc['name']); ?>">
                            <span>
                                <b><?php echo e($svc['name']); ?></b>
                                <small><?php echo e($svc['tagline']); ?></small>
                            </span>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div id="pkgSummary" class="promo-selection-summary empty">
                    No package selected yet
                </div>
                <input type="hidden" name="service" id="serviceInput" value="<?php echo e(array_key_first($services)); ?>" required>
                <input type="hidden" name="package" id="packageInput" required>
                <input type="hidden" name="tier" id="tierInput" required>

                <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $svcCode => $servicePackages): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $categoriesInService = collect($servicePackages)->pluck('category')->unique()->values()->all();
                ?>
                <div class="promo-panel service-packages" data-service-group="<?php echo e($svcCode); ?>" <?php echo e($loop->first ? '' : 'hidden'); ?>>

                    <?php if(count($categoriesInService) > 1): ?>
                        <?php
                            $catIcons = [
                                'all' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect></svg>',
                                'solo' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"></path></svg>',
                                'bundle' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="9" r="3.2"></circle><circle cx="16" cy="9" r="3.2"></circle><path d="M2.5 20c0-3.4 2.7-6 5.5-6s5.5 2.6 5.5 6M10.5 20c0-3.4 2.7-6 5.5-6s5.5 2.6 5.5 6"></path></svg>',
                                'packages' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8L12 3 3 8l9 5 9-5z"></path><path d="M3 8v8l9 5 9-5V8"></path><path d="M12 13v8"></path></svg>',];
                                 ?>
                        <div class="cat-filter" data-cat-filter>
                            <button type="button" class="active" data-cat="all">
                                <?php echo $catIcons['all']; ?>

                                All
                                <span class="cat-count"><?php echo e(count($servicePackages)); ?></span>
                            </button>
                            <?php $__currentLoopData = $categoriesInService; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" data-cat="<?php echo e($cat); ?>">
                                    <?php echo $catIcons[$cat] ?? ''; ?>

                                    <?php echo e($packageCategories[$cat] ?? ucfirst($cat)); ?>

                                    <span class="cat-count"><?php echo e(collect($servicePackages)->where('category', $cat)->count()); ?></span>
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    <div class="promo-grid" data-promo-grid>
                    <?php $__currentLoopData = $servicePackages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="promo-card" id="promoCard-<?php echo e($svcCode); ?>-<?php echo e($code); ?>" data-code="<?php echo e($code); ?>" data-category="<?php echo e($package['category']); ?>">
                            <div class="promo-media">
                                <img src="<?php echo e(asset('images/'.$package['image'])); ?>" alt="<?php echo e($package['name']); ?>" loading="lazy">
                                <span class="promo-badge"><?php echo e($package['badge']); ?></span>
                            </div>
                            <div class="promo-body">
                                <?php
                                    $pkgTitle = $package['name'];
                                    $pkgSub = null;
                                    if (preg_match('/^(.*?)\s*\((.*)\)\s*$/', $pkgTitle, $m)) {
                                        $pkgTitle = $m[1];
                                        $pkgSub = $m[2];
                                    }
                                ?>
                                <h4><?php echo e($pkgTitle); ?></h4>
                                <?php if($pkgSub): ?>
                                    <span class="promo-subtitle"><?php echo e($pkgSub); ?></span>
                                <?php endif; ?>
                                <p><?php echo e($package['desc']); ?></p>

                                <div class="promo-price-row">
                                    <div>
                                        <span class="promo-price-label">Starts at</span>
                                        <span class="promo-price">₱<?php echo e(number_format(min($package['tiers']))); ?></span>
                                    </div>
                                    <button type="button" class="u-btn promo-select-btn" data-toggle="<?php echo e($svcCode); ?>-<?php echo e($code); ?>">Select package</button>
                                </div>

                                <button type="button" class="promo-details-link" data-toggle="<?php echo e($svcCode); ?>-<?php echo e($code); ?>">View pax options &amp; inclusions ›</button>

                                <div class="promo-tiers" id="promoTiers-<?php echo e($svcCode); ?>-<?php echo e($code); ?>" hidden>
                                    <?php $__currentLoopData = $package['tiers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pax => $price): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <label class="promo-tier">
                                            <span style="display:flex;align-items:center;gap:8px;">
                                                <input type="radio" name="tier_choice" value="<?php echo e($svcCode); ?>|<?php echo e($code); ?>|<?php echo e($pax); ?>" data-pkg-name="<?php echo e($package['name']); ?>" data-pax="<?php echo e($pax); ?>" data-price="<?php echo e($price); ?>">
                                                <?php echo e($pax); ?> PAX
                                            </span>
                                            <b>₱<?php echo e(number_format($price)); ?></b>
                                        </label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <div class="promo-inclusions">
                                        <p class="promo-inclusions-title">What's included</p>
                                        <ul class="promo-inclusions-list" data-inclusions-list>
                                            <?php $__currentLoopData = $inclusions[$svcCode]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li <?php echo e($i >= 3 ? 'data-extra hidden' : ''); ?>><?php echo e($item); ?></li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                        <?php if(count($inclusions[$svcCode]) > 3): ?>
                                            <button type="button" class="promo-seemore-btn" data-seemore>See more (<?php echo e(count($inclusions[$svcCode]) - 3); ?>) ›</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <p class="promo-empty-filter" data-promo-empty hidden>No packages match this filter.</p>
                    <div class="promo-pagination" data-promo-pagination hidden>
                        <button type="button" data-page-prev aria-label="Previous page">&lsaquo;</button>
                        <span class="promo-page-info" data-page-info>1 / 1</span>
                        <button type="button" data-page-next aria-label="Next page">&rsaquo;</button>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </div>

        </div>
    </form>

    <!-- Booking Confirmation Modal -->
    <div class="booking-modal-overlay" id="bookingModalOverlay">
        <div class="booking-modal">
            <div class="booking-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px;">
                    <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                    <path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path>
                </svg>
            </div>
            <h3>Confirm Your Booking</h3>
            <p class="booking-modal-sub">Are you sure you want to book this package?</p>

            <div class="booking-modal-summary">
                <div class="booking-modal-row">
                    <span>Package</span>
                    <b id="confirmPackageText">—</b>
                </div>
                <div class="booking-modal-row">
                    <span>Date &amp; Time</span>
                    <b id="confirmDateTimeText">—</b>
                </div>
            </div>

            <div class="booking-modal-actions">
                <button type="button" class="btn-cancel" id="bookingModalCancel">Cancel</button>
                <button type="button" class="btn-confirm" id="bookingModalConfirm">Yes, Book Now</button>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    var btn    = document.getElementById('bookingSubmitBtn');
    var anchor = document.getElementById('bookingBtnAnchor');
    var form   = document.getElementById('bookingForm');
    if (!btn || !anchor || !form) return;

    var mq = window.matchMedia('(max-width: 768px)');

    function placeButton(isMobile) {
        if (isMobile) {
            form.appendChild(btn); // ilipat sa pinakadulo ng form
        } else {
            anchor.parentNode.insertBefore(btn, anchor); // ibalik sa dati
        }
    }

    placeButton(mq.matches);
    mq.addEventListener('change', function (e) { placeButton(e.matches); });
})();
</script>
<script>
(function () {
    var summaryEl = document.getElementById('pkgSummary');
    var serviceInput = document.getElementById('serviceInput');
    var packageInput = document.getElementById('packageInput');
    var tierInput = document.getElementById('tierInput');

    function peso(n) {
        return '₱' + Number(n).toLocaleString('en-PH');
    }

    function resetSelection() {
        packageInput.value = '';
        tierInput.value = '';
        document.querySelectorAll('.promo-tier').forEach(function (label) {
            label.classList.remove('selected');
        });
        document.querySelectorAll('.promo-card').forEach(function (card) {
            card.classList.remove('selected');
        });
        document.querySelectorAll('input[name="tier_choice"]').forEach(function (r) {
            r.checked = false;
        });
        summaryEl.classList.add('empty');
        summaryEl.textContent = 'No package selected yet';
        if (window.REKS_refreshSlots) window.REKS_refreshSlots();
    }

    document.querySelectorAll('.service-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            if (tab.classList.contains('active')) return;

            document.querySelectorAll('.service-tab').forEach(function (t) {
                t.classList.toggle('active', t === tab);
            });

            var svc = tab.dataset.service;
            serviceInput.value = svc;

            document.querySelectorAll('.service-packages').forEach(function (group) {
                group.hidden = group.dataset.serviceGroup !== svc;
            });

            // collapse any open tier panels from the previous service
            document.querySelectorAll('.promo-tiers').forEach(function (p) {
                p.setAttribute('hidden', '');
            });

            resetSelection();
        });
    });

    document.querySelectorAll('.promo-select-btn, .promo-details-link').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var key = btn.dataset.toggle;
            var panel = document.getElementById('promoTiers-' + key);
            var isHidden = panel.hasAttribute('hidden');

            // collapse any other open panel so only one is open at a time
            document.querySelectorAll('.promo-tiers').forEach(function (p) {
                if (p !== panel) p.setAttribute('hidden', '');
            });

            if (isHidden) {
                panel.removeAttribute('hidden');
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                panel.setAttribute('hidden', '');
            }
        });
    });

    document.querySelectorAll('input[name="tier_choice"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.promo-tier').forEach(function (label) {
                label.classList.remove('selected');
            });
            document.querySelectorAll('.promo-card').forEach(function (card) {
                card.classList.remove('selected');
            });
            radio.closest('.promo-tier').classList.add('selected');

            var parts = radio.value.split('|');
            var svc = parts[0];
            var code = parts[1];
            var pax = parts[2];

            document.getElementById('promoCard-' + svc + '-' + code).classList.add('selected');
            packageInput.value = code;
            tierInput.value = pax;
            if (window.REKS_refreshSlots) window.REKS_refreshSlots();

            summaryEl.classList.remove('empty');
            summaryEl.innerHTML = 'Selected: <b>' + radio.dataset.pkgName + '</b> · ' +
                pax + ' PAX · <b>' + peso(radio.dataset.price) + '</b>';
        });
    });

    document.querySelectorAll('[data-seemore]').forEach(function (btn) {
        var originalLabel = btn.textContent;
        btn.addEventListener('click', function () {
            var list = btn.previousElementSibling;
            var expanded = btn.classList.toggle('expanded');
            list.querySelectorAll('[data-extra]').forEach(function (li) {
                li.hidden = !expanded;
            });
            btn.textContent = expanded ? 'See less ‹' : originalLabel;
        });
    });

    // ---------------- category filter (replaces prev/next pagination) ----------------
    // Packages already arrive price-sorted ascending from the controller,
    // so filtering just shows/hides cards — no re-sorting needed here.
   function initCategoryFilter(group) {
    var filterBar  = group.querySelector('[data-cat-filter]');
    var grid       = group.querySelector('[data-promo-grid]');
    var emptyMsg   = group.querySelector('[data-promo-empty]');
    var pagination = group.querySelector('[data-promo-pagination]');
    var pageInfo   = group.querySelector('[data-page-info]');
    var prevBtn    = group.querySelector('[data-page-prev]');
    var nextBtn    = group.querySelector('[data-page-next]');
    if (!grid) return;

    var PAGE_SIZE = 4; // 2x2 — humigit-kumulang katapat ng height ng calendar
    var cards = Array.prototype.slice.call(grid.children);
    var matched = cards;
    var currentPage = 1;

    function renderPage() {
        var totalPages = Math.max(1, Math.ceil(matched.length / PAGE_SIZE));
        if (currentPage > totalPages) currentPage = totalPages;

        var start = (currentPage - 1) * PAGE_SIZE;
        var end = start + PAGE_SIZE;

        cards.forEach(function (card) { card.style.display = 'none'; });
        matched.slice(start, end).forEach(function (card) { card.style.display = ''; });

        if (emptyMsg) emptyMsg.hidden = matched.length > 0;

        if (pagination) {
            pagination.hidden = matched.length <= PAGE_SIZE;
            if (pageInfo) pageInfo.textContent = currentPage + ' / ' + totalPages;
            if (prevBtn) prevBtn.disabled = currentPage <= 1;
            if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
        }
    }

    function applyFilter(cat) {
        matched = cards.filter(function (card) {
            return cat === 'all' || card.dataset.category === cat;
        });
        currentPage = 1;
        renderPage();
    }

    if (filterBar) {
        filterBar.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                filterBar.querySelectorAll('button').forEach(function (b) {
                    b.classList.toggle('active', b === btn);
                });
                applyFilter(btn.dataset.cat);
            });
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                renderPage();
                grid.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            var totalPages = Math.max(1, Math.ceil(matched.length / PAGE_SIZE));
            if (currentPage < totalPages) {
                currentPage++;
                renderPage();
                grid.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    }

    applyFilter('all');
}

    document.querySelectorAll('.service-packages').forEach(initCategoryFilter);
})();
</script>
<script>
(function () {
    var today = new Date();
    today.setHours(0, 0, 0, 0);

    var state = {
        viewMonth: new Date(today.getFullYear(), today.getMonth(), 1),
        selectedDate: null,   // Date, midnight
        selectedTime: null,   // 'HH:mm' 24h internal value
        fmt: '12'
    };

    var monthLbl  = document.getElementById('calMonthLabel');
    var daysEl    = document.getElementById('calDays');
    var slotsEl   = document.getElementById('calSlots');
    var dayLbl    = document.getElementById('calSelectedDayLabel');
    var bar       = document.getElementById('calSelectedBar');
    var label     = document.getElementById('dateTimeLabel');
    var visitDateInput = document.getElementById('visitDateInput');
    var visitTimeInput = document.getElementById('visitTimeInput');
    var packageInput = document.getElementById('packageInput');

    // Visit duration (in hours) per package code. null = all-day pass
    // (no fixed duration — just needs a lead time before closing).
    var PACKAGE_DURATIONS = {
        non_exclusive: 3,
        exclusive_weekdays: 3,
        exclusive_weekends: 3,
        walkin_1hour: 1,
        walkin_2hour: 2,
        walkin_allday: null,
        weekday: 3,
        weekend: 3,
        group_bundle_1hour: 1,
        group_bundle_2hour: 2
    };

    function getSelectedDurationHours() {
        var code = packageInput.value;
        if (code && PACKAGE_DURATIONS.hasOwnProperty(code)) {
            return PACKAGE_DURATIONS[code]; // number of hours, or null = all-day
        }
        return 3; // fallback if nothing selected yet
    }

    function resetTimeSelection() {
        state.selectedTime = null;
        visitDateInput.value = '';
        visitTimeInput.value = '';
        bar.classList.add('empty');
        label.textContent = 'No date & time selected yet';
        renderSlots();
    }
    window.REKS_resetTimeSelection = resetTimeSelection;
    // Re-renders the time-slot grid for the currently selected package's
    // duration WITHOUT clearing the guest's already-chosen date/time —
    // used when switching service tabs or packages, so a selection made
    // earlier doesn't disappear. If the previously chosen time no longer
    // fits the new package duration, renderSlots() simply won't show it
    // as highlighted, but nothing is cleared.
    window.REKS_refreshSlots = function () {
        if (state.selectedDate) renderSlots();
    };

    var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var DAYS_SHORT = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function isWeekend(date) {
        var d = date.getDay(); // 0 = Sun, 6 = Sat
        return d === 0 || d === 6;
    }

    // Operating hours for the day.
    // Store closes at 9:00 PM every day — weekday closing time was
    // previously 10:00 PM, which produced booking slots that ran past
    // actual closing time (e.g. 7:00 PM - 10:00 PM for a 3-hour package).
    function hoursFor(date) {
        return isWeekend(date)
            ? { openHour: 10, closeHour: 21 } // 10:00 AM - 9:00 PM
            : { openHour: 9,  closeHour: 21 }; // 9:00 AM - 9:00 PM
    }

    function formatTime(hour, minute) {
        if (state.fmt === '24') {
            return pad(hour) + ':' + pad(minute);
        }
        var period = hour >= 12 ? 'PM' : 'AM';
        var h12 = hour % 12;
        if (h12 === 0) h12 = 12;
        return h12 + ':' + pad(minute) + ' ' + period;
    }

    function renderCalendar() {
        monthLbl.textContent = MONTHS[state.viewMonth.getMonth()] + ' ' + state.viewMonth.getFullYear();
        daysEl.innerHTML = '';

        var firstOfMonth = new Date(state.viewMonth.getFullYear(), state.viewMonth.getMonth(), 1);
        // Monday-first offset (0 = Monday ... 6 = Sunday)
        var offset = (firstOfMonth.getDay() + 6) % 7;
        var daysInMonth = new Date(state.viewMonth.getFullYear(), state.viewMonth.getMonth() + 1, 0).getDate();

        for (var i = 0; i < offset; i++) {
            var empty = document.createElement('span');
            empty.className = 'cal-day empty';
            daysEl.appendChild(empty);
        }

        for (var day = 1; day <= daysInMonth; day++) {
            var cellDate = new Date(state.viewMonth.getFullYear(), state.viewMonth.getMonth(), day);
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'cal-day';
            btn.textContent = day;

            var isPast = cellDate < today;
            if (isPast) {
                btn.classList.add('disabled');
                btn.disabled = true;
            }
            if (cellDate.getTime() === today.getTime()) {
                btn.classList.add('today');
            }
            if (state.selectedDate && cellDate.getTime() === state.selectedDate.getTime()) {
                btn.classList.add('selected');
            }

            (function (d) {
                btn.addEventListener('click', function () { selectDate(d); });
            })(cellDate);

            daysEl.appendChild(btn);
        }
    }

    function selectDate(date) {
        state.selectedDate = date;
        state.selectedTime = null;
        visitDateInput.value = '';
        visitTimeInput.value = '';
        bar.classList.add('empty');
        label.textContent = 'No date & time selected yet';
        renderCalendar();
        renderSlots();
    }

    function renderSlots() {
        slotsEl.innerHTML = '';

        if (!state.selectedDate) {
            dayLbl.textContent = 'Pick a date';
            var empty = document.createElement('p');
            empty.className = 'cal-slots-empty';
            empty.textContent = 'Select a date first';
            slotsEl.appendChild(empty);
            return;
        }

        dayLbl.textContent = DAYS_SHORT[state.selectedDate.getDay()] + ', ' +
            MONTHS[state.selectedDate.getMonth()] + ' ' + state.selectedDate.getDate();

        var hours = hoursFor(state.selectedDate);
        var duration = getSelectedDurationHours(); // hours, or null = all-day
        var lastStartHour = duration === null
            ? hours.closeHour - 1   // all-day pass: allow entry up to 1hr before closing
            : hours.closeHour - duration;
        var isToday = state.selectedDate.getTime() === today.getTime();
        var now = new Date();
        var hasSlots = false;

        for (var h = hours.openHour; h <= lastStartHour; h++) {
            [0, 30].forEach(function (m) {
                // don't go past the last allowed start time
                if (h === lastStartHour && m > 0) return;

                // hide past slots if the selected date is today
                if (isToday) {
                    var slotDate = new Date(state.selectedDate);
                    slotDate.setHours(h, m, 0, 0);
                    if (slotDate <= now) return;
                }

                hasSlots = true;
                var value = pad(h) + ':' + pad(m);
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'cal-slot';
                btn.textContent = duration === null
                    ? formatTime(h, m)
                    : formatTime(h, m) + ' – ' + formatTime((h + duration) % 24, m);
                btn.dataset.value = value;
                if (state.selectedTime === value) btn.classList.add('selected');

                btn.addEventListener('click', function () {
                    state.selectedTime = value;
                    commitSelection();
                    renderSlots();
                });

                slotsEl.appendChild(btn);
            });
        }

        if (!hasSlots) {
            var none = document.createElement('p');
            none.className = 'cal-slots-empty';
            none.textContent = 'No more slots available this day';
            slotsEl.appendChild(none);
        }
    }

    function commitSelection() {
        if (!state.selectedDate || !state.selectedTime) return;

        var y = state.selectedDate.getFullYear();
        var m = pad(state.selectedDate.getMonth() + 1);
        var d = pad(state.selectedDate.getDate());
        visitDateInput.value = y + '-' + m + '-' + d;
        visitTimeInput.value = state.selectedTime;

        var parts = state.selectedTime.split(':');
        var timeLabel = formatTime(parseInt(parts[0], 10), parseInt(parts[1], 10));
        label.innerHTML = 'Selected: <b>' + DAYS_SHORT[state.selectedDate.getDay()] + ', ' +
            MONTHS[state.selectedDate.getMonth()] + ' ' + d + ', ' + y + '</b> · <b>' + timeLabel + '</b>';
        bar.classList.remove('empty');
    }

    document.getElementById('calPrev').addEventListener('click', function () {
        state.viewMonth = new Date(state.viewMonth.getFullYear(), state.viewMonth.getMonth() - 1, 1);
        renderCalendar();
    });
    document.getElementById('calNext').addEventListener('click', function () {
        state.viewMonth = new Date(state.viewMonth.getFullYear(), state.viewMonth.getMonth() + 1, 1);
        renderCalendar();
    });

    document.querySelectorAll('.cal-fmt-toggle button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            state.fmt = btn.dataset.fmt;
            document.querySelectorAll('.cal-fmt-toggle button').forEach(function (b) {
                b.classList.toggle('active', b === btn);
            });
            renderSlots();
            if (state.selectedTime) commitSelection();
        });
    });

    // render immediately — calendar is always visible now, no open/close popup
    renderCalendar();
    renderSlots();
})();
</script>
<script>
// ── Confirmation modal + loading state on submit ──
(function () {
    var form = document.getElementById('bookingForm');
    var submitBtn = document.getElementById('bookingSubmitBtn');
    var overlay = document.getElementById('bookingModalOverlay');
    var confirmBtn = document.getElementById('bookingModalConfirm');
    var cancelBtn = document.getElementById('bookingModalCancel');
    var confirmPackageText = document.getElementById('confirmPackageText');
    var confirmDateTimeText = document.getElementById('confirmDateTimeText');
    if (!form || !submitBtn || !overlay) return;

    var originalLabel = submitBtn.textContent;
    var isConfirmed = false;

    function openModal() {
        var pkgSummary = document.getElementById('pkgSummary');
        var dateBar = document.getElementById('dateTimeLabel');
        confirmPackageText.textContent = pkgSummary ? pkgSummary.textContent.trim() : '—';
        confirmDateTimeText.textContent = dateBar ? dateBar.textContent.trim() : '—';
        overlay.classList.add('active');
    }

    function closeModal() {
        overlay.classList.remove('active');
    }

    form.addEventListener('submit', function (e) {
        // Pangalawang beses na — kumpirmado na, papayagan nating tuluyan
        if (isConfirmed) {
            if (submitBtn.disabled) {
                e.preventDefault();
                return;
            }
            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');
            submitBtn.innerHTML = '<span class="btn-spinner"></span> Booking...';

            setTimeout(function () {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('is-loading');
                    submitBtn.textContent = originalLabel;
                }
            }, 15000);
            return;
        }

        // Unang beses — i-validate muna, tapos ipakita ang modal sa halip na direktang mag-submit
        e.preventDefault();

        var visitDate = document.getElementById('visitDateInput').value;
        var visitTime = document.getElementById('visitTimeInput').value;
        var pkg = document.getElementById('packageInput').value;
        var tier = document.getElementById('tierInput').value;

        if (!visitDate || !visitTime || !pkg || !tier) {
            alert('Please pick a date, time, and package before booking.');
            return;
        }

        openModal();
    });

    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });

    confirmBtn.addEventListener('click', function () {
        isConfirmed = true;
        closeModal();
        if (form.requestSubmit) {
            form.requestSubmit(submitBtn);
        } else {
            form.submit();
        }
    });
})();
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\WONDERPARK\WONDERPARK2\laravel-app\resources\views/user/booking.blade.php ENDPATH**/ ?>