<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Self Check-In</title>
<style>
    :root {
        --pink: #e8174a;
        --ink: #1a1a2e;
        --cream: #fdf6f0;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: 'Segoe UI', Arial, sans-serif;
        background: var(--cream);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }
    .card {
        background: #fff;
        width: 100%;
        max-width: 420px;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(26, 26, 46, 0.15);
        overflow: hidden;
    }
    .card-header {
        padding: 24px 28px 16px;
        border-bottom: 1px solid #f1e6e6;
    }
    .card-header .eyebrow {
        color: var(--pink);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin: 0 0 4px;
    }
    .card-header h1 {
        color: var(--ink);
        font-size: 20px;
        margin: 0;
    }
    .card-body {
        padding: 24px 28px 28px;
    }
    label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--ink);
        margin-bottom: 6px;
    }
    select {
        width: 100%;
        padding: 12px 14px;
        border-radius: 10px;
        border: 1px solid #e2d6d6;
        font-size: 15px;
        color: var(--ink);
        background: #fff;
        margin-bottom: 18px;
    }
    select:focus {
        outline: none;
        border-color: var(--pink);
    }
    .btn-row {
        display: flex;
        gap: 12px;
    }
    button.action-btn {
        flex: 1;
        padding: 14px 0;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.1s ease, opacity 0.2s ease;
    }
    button.action-btn:active {
        transform: scale(0.97);
    }
    button.action-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    .btn-in {
        background: var(--pink);
        color: #fff;
    }
    .btn-out {
        background: var(--ink);
        color: #fff;
    }
    .message-box {
        margin-top: 18px;
        padding: 14px 16px;
        border-radius: 10px;
        font-size: 14px;
        display: none;
    }
    .message-box.success {
        display: block;
        background: #eafaf0;
        color: #1e7b45;
        border: 1px solid #bdeccd;
    }
    .message-box.error {
        display: block;
        background: #fdeaea;
        color: #b3261e;
        border: 1px solid #f5c2c2;
    }
    .hint {
        font-size: 12px;
        color: #8a8a99;
        margin-top: 14px;
        text-align: center;
    }
</style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <p class="eyebrow">Employee Self Check-In</p>
        <h1>Attendance QR Code</h1>
    </div>

    <div class="card-body">
        <form id="checkinForm">
            @csrf

            <label for="employee_name">Select your name</label>
            <select id="employee_name" name="employee_name" required>
                <option value="" disabled selected>-- Choose your name --</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->employee_name }}" data-category="{{ $employee->category }}">
                        {{ $employee->employee_name }}
                    </option>
                @endforeach
            </select>

            <input type="hidden" id="category" name="category" value="">

            <div class="btn-row">
                <button type="button" class="action-btn btn-in" id="btnTimeIn">Time In</button>
                <button type="button" class="action-btn btn-out" id="btnTimeOut">Time Out</button>
            </div>
        </form>

        <div id="messageBox" class="message-box"></div>

        <p class="hint">Pumili ng pangalan bago pindutin ang Time In / Time Out.</p>
    </div>
</div>

<script>
    const employeeSelect = document.getElementById('employee_name');
    const categoryInput = document.getElementById('category');
    const btnTimeIn = document.getElementById('btnTimeIn');
    const btnTimeOut = document.getElementById('btnTimeOut');
    const messageBox = document.getElementById('messageBox');
    const csrfToken = document.querySelector('input[name="_token"]').value;

    employeeSelect.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        categoryInput.value = selected ? (selected.getAttribute('data-category') || '') : '';
    });

    function showMessage(text, type) {
        messageBox.textContent = text;
        messageBox.className = 'message-box ' + type;
    }

    function submitCheckin(type) {
        if (!employeeSelect.value) {
            showMessage('Pumili muna ng pangalan.', 'error');
            return;
        }

        btnTimeIn.disabled = true;
        btnTimeOut.disabled = true;

        fetch("{{ url('/attendance/checkin') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                employee_name: employeeSelect.value,
                category: categoryInput.value,
                type: type,
            }),
        })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (res.ok) {
                showMessage(data.message || 'Naitala ang iyong attendance.', 'success');
            } else {
                showMessage(data.message || 'May problema, subukan ulit.', 'error');
            }
        })
        .catch(() => {
            showMessage('Hindi makakonekta sa server. Subukan ulit.', 'error');
        })
        .finally(() => {
            btnTimeIn.disabled = false;
            btnTimeOut.disabled = false;
        });
    }

    btnTimeIn.addEventListener('click', () => submitCheckin('in'));
    btnTimeOut.addEventListener('click', () => submitCheckin('out'));
</script>

</body>
</html>