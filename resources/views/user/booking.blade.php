{{--
    resources/views/user/booking.blade.php
    Route: GET  /user/booking  ->  user.booking
           POST /user/booking  ->  user.booking.store
    Controller: App\Http\Controllers\User\BookingController@create / store

    Expects from the controller:
      $packages -> array keyed by package code, each ['name','desc','price']

    Layout: two-column 50/50 split.
      - LEFT  column: a big, always-visible (no popup) calendar + time slots,
        so the visit date/time being booked is clearly visible at all times.
      - RIGHT column: "Choose your experience" service switch + package
        cards, paginated (prev/next + page numbers) so the column never
        turns into one long scroll.

    Date & time are picked straight from the inline calendar (built client
    side in JS, always reflects the real current date/month). Visit duration
    depends on the selected package (see PACKAGE_DURATIONS in the script
    below) instead of a flat 3 hours:
      - Weekdays (Mon-Fri): open 9:00 AM  - 9:00 PM
      - Weekends (Sat-Sun): open 10:00 AM - 9:00 PM
--}}
@extends('layouts.user')

@section('title', 'Booking')
@section('page-title', 'Booking')
@section('page-subtitle', 'Pick a date and package')
@section('body-class', 'page-uniform')

@section('content')

    <form method="POST" action="{{ route('user.booking.store') }}" id="bookingForm">
        @csrf

        <div class="booking-split">

            {{-- ================= LEFT: big inline calendar ================= --}}
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
            </div>

            {{-- ================= RIGHT: experience + paginated packages ================= --}}
            <div class="booking-col booking-col-pkg">
                <div class="service-switch" id="serviceSwitch">
                    @foreach ($services as $svcCode => $svc)
                        <button type="button" class="service-tab {{ $loop->first ? 'active' : '' }}" data-service="{{ $svcCode }}">
                            <img src="{{ asset('images/'.$svc['image']) }}" alt="{{ $svc['name'] }}">
                            <span>
                                <b>{{ $svc['name'] }}</b>
                                <small>{{ $svc['tagline'] }}</small>
                            </span>
                        </button>
                    @endforeach
                </div>

                <div id="pkgSummary" class="promo-selection-summary empty">
                    No package selected yet
                </div>
                <input type="hidden" name="service" id="serviceInput" value="{{ array_key_first($services) }}" required>
                <input type="hidden" name="package" id="packageInput" required>
                <input type="hidden" name="tier" id="tierInput" required>

                @foreach ($packages as $svcCode => $servicePackages)
                <div class="promo-panel service-packages" data-service-group="{{ $svcCode }}" {{ $loop->first ? '' : 'hidden' }}>
                    <div class="promo-grid" data-promo-grid>
                    @foreach ($servicePackages as $code => $package)
                        <div class="promo-card" id="promoCard-{{ $svcCode }}-{{ $code }}" data-code="{{ $code }}">
                            <div class="promo-media">
                                <img src="{{ asset('images/'.$package['image']) }}" alt="{{ $package['name'] }}" loading="lazy">
                                @if (!empty($package['badge']))
                                <span class="promo-badge">{{ $package['badge'] }}</span>
                                @endif
                            </div>
                            <div class="promo-body">
                                @php
                                    $pkgTitle = $package['name'];
                                    $pkgSub = null;
                                    if (preg_match('/^(.*?)\s*\((.*)\)\s*$/', $pkgTitle, $m)) {
                                        $pkgTitle = $m[1];
                                        $pkgSub = $m[2];
                                    }
                                @endphp
                                <h4>{{ $pkgTitle }}</h4>
                                @if ($pkgSub)
                                    <span class="promo-subtitle">{{ $pkgSub }}</span>
                                @endif
                                <p>{{ $package['desc'] }}</p>

                                <div class="promo-price-row">
                                    <div>
                                        <span class="promo-price-label">Starts at</span>
                                        <span class="promo-price">₱{{ number_format(min($package['tiers'])) }}</span>
                                    </div>
                                    <button type="button" class="u-btn promo-select-btn" data-toggle="{{ $svcCode }}-{{ $code }}">Select package</button>
                                </div>

                                <button type="button" class="promo-details-link" data-toggle="{{ $svcCode }}-{{ $code }}">View pax options &amp; inclusions ›</button>

                                <div class="promo-tiers" id="promoTiers-{{ $svcCode }}-{{ $code }}" hidden>
                                    @foreach ($package['tiers'] as $pax => $price)
                                        <label class="promo-tier">
                                            <span style="display:flex;align-items:center;gap:8px;">
                                                <input type="radio" name="tier_choice" value="{{ $svcCode }}|{{ $code }}|{{ $pax }}" data-pkg-name="{{ $package['name'] }}" data-pax="{{ $pax }}" data-price="{{ $price }}">
                                                {{ $pax }} PAX
                                            </span>
                                            <b>₱{{ number_format($price) }}</b>
                                        </label>
                                    @endforeach

                                    <div class="promo-inclusions">
                                        <p class="promo-inclusions-title">What's included</p>
                                        <ul class="promo-inclusions-list" data-inclusions-list>
                                            @foreach ($inclusions[$svcCode][$code] as $i => $item)
                                                <li {{ $i >= 3 ? 'data-extra hidden' : '' }}>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                        @if (count($inclusions[$svcCode][$code]) > 3)
                                            <button type="button" class="promo-seemore-btn" data-seemore>See more ({{ count($inclusions[$svcCode][$code]) - 3 }}) ›</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>

                    <div class="promo-pagination" data-promo-pagination hidden></div>
                </div>
                @endforeach

                <button type="submit" class="u-btn booking-submit-btn">+ Booking</button>
            </div>

        </div>
    </form>

@endsection

@push('scripts')
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

    // ---------------- package pagination (prev / numbers / next) ----------------
    var PROMO_PAGE_SIZE = 2;

    function initPagination(group) {
        var grid = group.querySelector('[data-promo-grid]');
        var pager = group.querySelector('[data-promo-pagination]');
        if (!grid || !pager) return;

        var cards = Array.prototype.slice.call(grid.children);
        var totalPages = Math.max(1, Math.ceil(cards.length / PROMO_PAGE_SIZE));
        var page = 1;

        function render() {
            cards.forEach(function (card, i) {
                var cardPage = Math.floor(i / PROMO_PAGE_SIZE) + 1;
                card.style.display = (cardPage === page) ? '' : 'none';
            });

            pager.innerHTML = '';
            if (totalPages <= 1) {
                pager.hidden = true;
                return;
            }
            pager.hidden = false;

            var prev = document.createElement('button');
            prev.type = 'button';
            prev.className = 'pg-btn pg-prev';
            prev.innerHTML = '&lsaquo;';
            prev.disabled = (page === 1);
            prev.addEventListener('click', function () {
                if (page > 1) { page--; render(); scrollGridIntoView(); }
            });
            pager.appendChild(prev);

            for (var p = 1; p <= totalPages; p++) {
                (function (p) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'pg-btn pg-num' + (p === page ? ' active' : '');
                    b.textContent = p;
                    b.addEventListener('click', function () {
                        page = p; render(); scrollGridIntoView();
                    });
                    pager.appendChild(b);
                })(p);
            }

            var next = document.createElement('button');
            next.type = 'button';
            next.className = 'pg-btn pg-next';
            next.innerHTML = '&rsaquo;';
            next.disabled = (page === totalPages);
            next.addEventListener('click', function () {
                if (page < totalPages) { page++; render(); scrollGridIntoView(); }
            });
            pager.appendChild(next);
        }

        function scrollGridIntoView() {
            grid.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        render();
    }

    document.querySelectorAll('.service-packages').forEach(initPagination);
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
@endpush
