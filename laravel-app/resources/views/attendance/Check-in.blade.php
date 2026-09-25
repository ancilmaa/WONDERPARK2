<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Attendance Check-In &middot; WonderPark</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --pink-deep: #C2185B;
            --pink-dark: #A31650;
            --pink-light: #FBD9E6;
            --pink-pale: #FDF1F5;
            --ink: #221A2B;
            --ink-soft: #5C5468;
            --muted: #9A93A6;
            --bg: #F7F3F8;
            --card: #ffffff;
            --line: #EFE7F1;
            --present: #1FAE9E;
            --deduct: #D63E63;
            --shadow-sm: 0 2px 10px rgba(34,26,43,.06);
            --shadow-md: 0 12px 32px rgba(34,26,43,.14);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(160deg, var(--pink-pale), var(--bg));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--ink);
        }

        .card {
            background: var(--card);
            border-radius: 22px;
            box-shadow: var(--shadow-md);
            width: 100%;
            max-width: 400px;
            padding: 34px 28px;
        }

        .brand {
            text-align: center;
            margin-bottom: 22px;
        }

        .brand .eyebrow {
            font-size: .68rem;
            font-weight: 700;
            color: var(--pink-deep);
            text-transform: uppercase;
            letter-spacing: .1em;
            margin-bottom: 6px;
        }

        .brand h1 {
            font-family: 'Source Serif 4', serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .brand p {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 6px;
        }

        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            color: var(--ink-soft);
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 7px;
        }

        .field select {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid var(--line);
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            background: var(--bg);
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7' viewBox='0 0 12 7'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%235C5468' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }

        .field select:focus {
            outline: none;
            border-color: var(--pink-deep);
            background-color: #fff;
        }

        .now {
            text-align: center;
            background: var(--pink-pale);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 22px;
        }

        .now .time {
            font-family: 'Source Serif 4', serif;
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--pink-deep);
            line-height: 1;
        }

        .now .date {
            font-size: 12px;
            color: var(--ink-soft);
            margin-top: 6px;
        }

        .btn-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        button.action {
            padding: 16px 10px;
            border-radius: 14px;
            border: none;
            font-size: 14.5px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            transition: transform .1s ease, box-shadow .15s ease;
        }

        button.action:active {
            transform: scale(.97);
        }

        button.action i {
            font-size: 20px;
        }

        #btnIn {
            background: var(--present);
            color: #fff;
        }

        #btnIn:hover {
            box-shadow: 0 0 0 4px rgba(31,174,158,.2);
        }

        #btnOut {
            background: var(--deduct);
            color: #fff;
        }

        #btnOut:hover {
            box-shadow: 0 0 0 4px rgba(214,62,99,.2);
        }

        button.action:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .flash {
            margin-top: 20px;
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.5;
            display: none;
        }

        .flash.show {
            display: block;
        }

        .flash.success {
            background: #E3F6F3;
            color: #147E71;
        }

        .flash.error {
            background: #FFE3EB;
            color: var(--deduct);
        }

        .hint {
            text-align: center;
            font-size: 11.5px;
            color: var(--muted);
            margin-top: 18px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="brand">
            <div class="eyebrow">WonderPark &middot; Lipa Branch</div>
            <h1>Attendance Check-In</h1>
            <p>Pumili ng pangalan, tapos i-tap ang Time In o Time Out.</p>
        </div>

        <div class="now">
            <div class="time" id="liveClock">--:--</div>
            <div class="date" id="liveDate">&nbsp;</div>
        </div>

        <form id="checkinForm">
            @csrf
            <div class="field">
                <label for="employee_select">Pangalan</label>
                <select id="employee_select" required>
                    <option value="" disabled selected>Piliin ang iyong pangalan&hellip;</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->employee_name }}" data-category="{{ $emp->category }}">
                            {{ $emp->employee_name }} &middot; {{ ucwords(strtolower($emp->category)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="btn-row">
                <button type="button" class="action" id="btnIn">
                    <i class="fa-solid fa-right-to-bracket"></i> Time In
                </button>
                <button type="button" class="action" id="btnOut">
                    <i class="fa-solid fa-right-from-bracket"></i> Time Out
                </button>
            </div>
        </form>

        <div class="flash" id="flashMsg"></div>

        <p class="hint">Kung nagkamali ka ng pindot o may isyu, lumapit lang sa HR/Admin.</p>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const timeOpts = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            const dateOpts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('liveClock').textContent = now.toLocaleTimeString('en-PH', timeOpts);
            document.getElementById('liveDate').textContent = now.toLocaleDateString('en-PH', dateOpts);
        }
        updateClock();
        setInterval(updateClock, 1000);

        const select = document.getElementById('employee_select');
        const btnIn = document.getElementById('btnIn');
        const btnOut = document.getElementById('btnOut');
        const flash = document.getElementById('flashMsg');
        const csrfToken = document.querySelector('input[name="_token"]')?.value;

        function showFlash(msg, type) {
            flash.textContent = msg;
            flash.className = `flash show ${type}`;
        }

        async function submitCheckin(type) {
            if (!select.value) {
                showFlash('Pumili muna ng pangalan bago mag-check in/out.', 'error');
                return;
            }

            const category = select.selectedOptions[0]?.dataset.category || '';

            btnIn.disabled = true;
            btnOut.disabled = true;

            try {
                const res = await fetch('{{ route('attendance.checkin.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        employee_name: select.value,
                        category: category,
                        type: type,
                    }),
                });

                const data = await res.json().catch(() => null);

                if (res.ok) {
                    showFlash((data && data.message) || 'Nai-record na ang iyong attendance!', 'success');
                } else {
                    showFlash((data && data.message) || 'May problema, subukan ulit.', 'error');
                }
            } catch (err) {
                showFlash('Walang internet connection. Subukan ulit.', 'error');
            } finally {
                btnIn.disabled = false;
                btnOut.disabled = false;
            }
        }

        btnIn.addEventListener('click', () => submitCheckin('in'));
        btnOut.addEventListener('click', () => submitCheckin('out'));
    </script>

</body>
</html>