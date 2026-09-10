{{--
    resources/views/user/reschedule.blade.php
    Route: GET  /user/bookings/{booking}/reschedule    -> user.bookings.reschedule.edit
           POST /user/bookings/{booking}/reschedule    -> user.bookings.reschedule
    Controller: App\Http\Controllers\User\BookingController@editReschedule / reschedule

    Expects from the controller:
      $booking      -> App\Models\Booking (service, package, tier, visit_date, visit_time)
      $packageLabel -> string, the display name of the package
--}}
@extends('layouts.user')

@section('title', 'Reschedule Booking')
@section('page-title', 'Reschedule')
@section('page-subtitle', 'Pick a new date and time')
@section('body-class', 'page-uniform')

@section('content')

    <div class="u-card">
        <h4>{{ $packageLabel }}</h4>
        <p>Currently set for {{ $booking->visit_date->format('M j, Y') }} · {{ $booking->tier }} pax</p>
    </div>

    <form method="POST" action="{{ route('user.bookings.reschedule', $booking->id) }}" id="rescheduleForm">
        @csrf

        <div class="cal-embed" id="calEmbed" style="margin-top:14px;">
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

        <div id="calSelectedBar" class="promo-selection-summary empty" style="margin-top:14px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;flex-shrink:0;">
                <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                <path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path>
            </svg>
            <span id="dateTimeLabel">No date &amp; time selected yet</span>
        </div>

        <input type="hidden" name="visit_date" id="visitDateInput" required>
        <input type="hidden" name="visit_time" id="visitTimeInput" required>
        <input type="hidden" id="packageInput" value="{{ $booking->package }}">

        <button type="submit" class="u-btn" style="margin-top:14px;">Save New Date &amp; Time</button>
    </form>

    <a href="{{ route('user.bookings') }}" class="u-btn ghost" style="margin-top:10px;">Cancel</a>

@endsection

@push('scripts')
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
    // Keep this in sync with the same map in booking.blade.php.
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
        return 3; // fallback for packages without a fixed duration (e.g. per-ride tickets)
    }

    var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var DAYS_SHORT = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function isWeekend(date) {
        var d = date.getDay(); // 0 = Sun, 6 = Sat
        return d === 0 || d === 6;
    }

    // Store closes at 9:00 PM every day.
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
        var offset = (firstOfMonth.getDay() + 6) % 7; // Monday-first
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
        var duration = getSelectedDurationHours();
        var lastStartHour = duration === null
            ? hours.closeHour - 1
            : hours.closeHour - duration;
        var isToday = state.selectedDate.getTime() === today.getTime();
        var now = new Date();
        var hasSlots = false;

        for (var h = hours.openHour; h <= lastStartHour; h++) {
            [0, 30].forEach(function (m) {
                if (h === lastStartHour && m > 0) return;

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

    renderCalendar();
    renderSlots();
})();
</script>
@endpush
