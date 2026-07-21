@extends('layouts.sidebar')

@php
    $zoneIcons = [
        'Roller Fever'   => 'fa-bolt',
        'Field of Rides' => 'fa-brain',
        'Dino Adventure' => 'fa-dragon',
    ];
    $zoneIcon = $zoneIcons[$zone] ?? 'fa-chart-line';
@endphp

@section('title', $zone . ' Forecast')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
@endpush

@section('styles')
<style>

  /* ============================================================
     DESIGN TOKENS — indigo / coral dashboard system
     (shared with Inventory Forecast for a consistent product feel)
     ============================================================ */
  :root{
    --fc-bg:        #F5F6FB;
    --fc-card:      #FFFFFF;
    --fc-dark:      #1E1B3A;
    --fc-ink:       #1B1D28;
    --fc-ink-soft:  #585B72;
    --fc-muted:     #9195AA;
    --fc-line:      #ECEDF6;

    --fc-primary:      #6C5CE0;
    --fc-primary-dark: #5445C4;
    --fc-primary-light:#EFEBFF;

    --fc-accent:       #FF7A59;
    --fc-accent-light: #FFEDE6;

    --fc-teal:   #22C3AE;
    --fc-teal-bg:#E3F9F5;
    --fc-amber:  #FFB020;
    --fc-amber-bg:#FFF4DE;
    --fc-red:    #F0506E;
    --fc-red-bg: #FDE9ED;

    --fc-shadow-sm: 0 2px 10px rgba(30,27,58,.05);
    --fc-shadow-md: 0 10px 28px rgba(30,27,58,.10);
    --fc-radius-lg: 20px;
    --fc-radius-md: 14px;
    --fc-radius-sm: 10px;
  }

  /* ===== MAIN ===== */
  .content-body{padding:clamp(16px, 3vw, 36px);max-width:1320px;margin:0 auto;background:var(--fc-bg);}
  .jak{ font-family:'Plus Jakarta Sans','Inter',sans-serif; }

  .back-link{
    display:inline-flex;align-items:center;gap:8px;color:var(--fc-ink-soft);font-weight:600;font-size:.82rem;
    margin-bottom:16px;transition:.15s;
  }
  .back-link:hover{color:var(--fc-primary-dark);}

  /* ===== TOOLBAR ===== */
  .toolbar{
      background:var(--fc-card);
      padding:22px 26px;
      margin-bottom:20px;
      border-radius:var(--fc-radius-lg);
      box-shadow:var(--fc-shadow-sm);
      display:flex; justify-content:space-between; align-items:center;
      flex-wrap:wrap; gap:18px;
  }
  .toolbar .eyebrow{ font-size:.7rem; font-weight:700; color:var(--fc-primary); text-transform:uppercase; letter-spacing:.09em; margin-bottom:6px; }
  .toolbar h2{
    font-family:'Plus Jakarta Sans',sans-serif; font-size:1.5rem; font-weight:800; color:var(--fc-ink);
    display:flex; align-items:center; gap:10px; letter-spacing:-.01em;
  }
  .toolbar h2 i{
    width:38px;height:38px;border-radius:12px;background:var(--fc-primary-light);color:var(--fc-primary);
    display:inline-flex;align-items:center;justify-content:center;font-size:1rem;
  }
  .toolbar p{ color:var(--fc-muted); font-size:.84rem; margin-top:5px; }
  .live-chip{
    display:inline-flex;align-items:center;gap:7px;background:var(--fc-primary-light);
    color:var(--fc-primary-dark);padding:9px 16px;border-radius:999px;font-size:.78rem;font-weight:700;
  }
  .live-chip::before{content:'';width:7px;height:7px;border-radius:50%;background:var(--fc-teal);box-shadow:0 0 0 3px var(--fc-teal-bg);}

  /* ===== FILTER BAR ===== */
  .filter-bar{
    display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;
    background:var(--fc-card);border:1px solid var(--fc-line);border-radius:var(--fc-radius-md);
    padding:16px 18px;margin-bottom:20px;box-shadow:var(--fc-shadow-sm);
  }
  .filter-field{display:flex;flex-direction:column;gap:6px;flex:1 1 150px;min-width:140px;}
  .filter-field label{font-size:.66rem;font-weight:700;color:var(--fc-muted);text-transform:uppercase;letter-spacing:.06em;}
  .filter-field input, .filter-field select{
    border:1px solid var(--fc-line);border-radius:var(--fc-radius-sm);padding:9px 12px;
    font-size:.82rem;font-family:inherit;color:var(--fc-ink);background:var(--fc-bg);width:100%;
  }
  .filter-field input:focus, .filter-field select:focus{outline:none;border-color:var(--fc-primary);box-shadow:0 0 0 3px var(--fc-primary-light);}
  .filter-btn{
    background:var(--fc-primary);color:#fff;border:none;border-radius:var(--fc-radius-sm);padding:10px 20px;
    font-weight:700;font-size:.82rem;cursor:pointer;display:flex;align-items:center;gap:7px;transition:.15s;height:39px;flex:0 0 auto;
  }
  .filter-btn:hover{background:var(--fc-primary-dark);}
  .filter-btn-clear{
    background:var(--fc-bg);color:var(--fc-ink-soft);border:1px solid var(--fc-line);
    border-radius:var(--fc-radius-sm);padding:10px 18px;font-weight:700;font-size:.82rem;cursor:pointer;height:39px;flex:0 0 auto;
  }
  .filter-btn-clear:hover{border-color:var(--fc-primary);color:var(--fc-primary-dark);}

  /* ===== PERCENTAGE BARS ===== */
  .pct-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--fc-line);flex-wrap:wrap;}
  .pct-row:last-child{border-bottom:none;}
  .pct-label{width:160px;flex-shrink:0;font-size:.82rem;font-weight:700;color:var(--fc-ink);}
  .pct-track{flex:1;min-width:100px;height:20px;background:var(--fc-primary-light);border-radius:20px;overflow:hidden;position:relative;}
  .pct-fill{height:100%;border-radius:20px;background:linear-gradient(90deg,var(--fc-primary),var(--fc-accent));display:flex;align-items:center;justify-content:flex-end;padding-right:8px;transition:width .5s ease;}
  .pct-fill span{color:#fff;font-size:.68rem;font-weight:700;white-space:nowrap;}
  .pct-value{width:56px;flex-shrink:0;text-align:right;font-size:.82rem;font-weight:800;color:var(--fc-primary-dark);}
  .best-tag{background:var(--fc-teal-bg);color:var(--fc-teal);padding:2px 10px;border-radius:20px;font-size:.65rem;font-weight:700;margin-left:8px;}
  .worst-tag{background:var(--fc-red-bg);color:var(--fc-red);padding:2px 10px;border-radius:20px;font-size:.65rem;font-weight:700;margin-left:8px;}
  .type-heading{font-size:.9rem;font-weight:800;color:var(--fc-primary-dark);margin:20px 0 10px;display:flex;align-items:center;gap:8px;}
  .type-heading:first-child{margin-top:0;}

  /* ===== STAT CARDS ===== */
  .metrics-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px;}
  @media(max-width:980px){ .metrics-grid{grid-template-columns:repeat(2,1fr);} }
  @media(max-width:480px){ .metrics-grid{grid-template-columns:1fr;} }

  .metric-card{
    background:var(--fc-card);border:none;border-radius:var(--fc-radius-lg);padding:20px 22px;
    box-shadow:var(--fc-shadow-sm);position:relative;overflow:hidden;transition:.18s;
  }
  .metric-card:hover{box-shadow:var(--fc-shadow-md);transform:translateY(-2px);}
  .metric-card:first-child{background:var(--fc-dark);}
  .metric-card:first-child .metric-label{color:rgba(255,255,255,.55);}
  .metric-card:first-child .metric-value{color:#fff;}
  .metric-card:first-child .metric-sub{color:rgba(255,255,255,.6);}
  .metric-label{font-size:.68rem;color:var(--fc-muted);font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;}
  .metric-value{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.7rem;font-weight:800;color:var(--fc-ink);line-height:1;letter-spacing:-.02em;}
  .metric-sub{font-size:11px;color:var(--fc-muted);margin-top:8px;font-weight:600;}

  /* ===== SECTION CARD / TABS ===== */
  .section-card{background:var(--fc-card);border:none;border-radius:var(--fc-radius-lg);box-shadow:var(--fc-shadow-sm);margin-bottom:24px;overflow:hidden;}
  .tab-bar{
    display:flex;gap:6px;padding:14px 14px 0;border-bottom:1px solid var(--fc-line);flex-wrap:nowrap;
    background:var(--fc-bg);overflow-x:auto;scrollbar-width:none;
  }
  .tab-bar::-webkit-scrollbar{display:none;}
  .tab-btn{
    flex:0 0 auto;
    padding:9px 16px;border:none;background:none;cursor:pointer;font-weight:600;font-size:.82rem;
    color:var(--fc-ink-soft);border-radius:10px 10px 0 0;margin-bottom:-1px;transition:.15s;font-family:'Inter',sans-serif;white-space:nowrap;
  }
  .tab-btn.active{color:var(--fc-primary-dark);background:var(--fc-card);box-shadow:0 -3px 0 var(--fc-primary) inset;}
  .tab-btn:hover:not(.active){color:var(--fc-primary-dark);background:rgba(108,92,224,.08);}
  .tab-pane{display:none;padding:26px;}
  .tab-pane.active{display:block;animation:fcFade .25s ease;}
  @keyframes fcFade{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:translateY(0)}}

  .pane-title{margin:0 0 14px;font-family:'Plus Jakarta Sans',sans-serif;font-size:1rem;font-weight:700;color:var(--fc-ink);display:flex;align-items:center;gap:8px;}

  .chart-wrap{position:relative;height:300px;margin-bottom:16px;}

  .info-note{
    background:var(--fc-primary-light);border:1px solid #DED6FA;border-radius:var(--fc-radius-md);
    padding:13px 16px;margin-bottom:18px;font-size:.8rem;color:var(--fc-ink-soft);
  }
  .info-note strong{color:var(--fc-primary-dark);}

  /* ===== TABLES ===== */
  .ml-table{width:100%;border-collapse:collapse;font-size:.82rem;}
  .ml-table th{background:var(--fc-dark);color:#fff;padding:12px 14px;text-align:left;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.03em;border-bottom:none;}
  .ml-table td{padding:12px 14px;border-bottom:1px solid var(--fc-line);color:var(--fc-ink-soft);}
  .ml-table tr:last-child td{border-bottom:none;}
  .ml-table tr:hover td{background:var(--fc-primary-light);}
  .cat-row:hover td{background:var(--fc-primary-light) !important;}
  .table-scroll{width:100%;overflow-x:auto;}

  /* ===== BADGES ===== */
  .trend-up{background:var(--fc-teal-bg);color:var(--fc-teal);padding:4px 12px;border-radius:20px;font-size:.72rem;font-weight:700;}
  .trend-down{background:var(--fc-red-bg);color:var(--fc-red);padding:4px 12px;border-radius:20px;font-size:.72rem;font-weight:700;}
  .trend-stable{background:var(--fc-amber-bg);color:#946600;padding:4px 12px;border-radius:20px;font-size:.72rem;font-weight:700;}
  .peak-badge{background:var(--fc-primary);color:#fff;padding:6px 14px;border-radius:20px;font-size:.72rem;font-weight:700;display:inline-block;margin:3px;}
  .staff-card{background:var(--fc-primary-light);border-left:4px solid var(--fc-primary);padding:12px 16px;border-radius:var(--fc-radius-sm);margin-bottom:10px;}
  .staff-card .hour{font-weight:700;color:var(--fc-primary-dark);font-size:.88rem;}
  .staff-card .note{color:var(--fc-ink-soft);font-size:.78rem;margin-top:4px;}

  /* ===== LOADING / ERROR ===== */
  .loading{text-align:center;padding:40px;color:var(--fc-muted);}
  .loading i{font-size:28px;animation:spin 1s linear infinite;color:var(--fc-primary);}
  @keyframes spin{to{transform:rotate(360deg)}}
  .error-msg{background:var(--fc-red-bg);color:var(--fc-red);padding:14px;border-radius:var(--fc-radius-sm);text-align:center;font-size:.82rem;}

  /* ===== TOAST ===== */
  .toast{position:fixed;bottom:20px;right:20px;background:var(--fc-dark);color:#fff;padding:12px 18px;border-radius:var(--fc-radius-sm);display:none;box-shadow:var(--fc-shadow-md);z-index:3000;font-weight:700;font-size:.82rem;}
  .toast.error{background:var(--fc-red);}

  /* ===== RESPONSIVE ===== */
  @media(max-width:900px){
    .content-body{padding:16px;}
    .toolbar{flex-direction:column;align-items:flex-start;}
  }
  @media(max-width:640px){
    .tab-pane{padding:18px;}
    .chart-wrap{height:230px;}
  }
</style>
@endsection

@section('content')

<div class="toolbar">
  <div>
    <div class="eyebrow">{{ $zone }} &middot; ML Forecast</div>
    <h2><i class="fa-solid {{ $zoneIcon }}"></i> {{ $zone }} Forecast</h2>
    <p>ML-powered sales trends and demand predictions for {{ $zone }}</p>
  </div>
  <span class="live-chip">Live · <span id="generatedAt">Loading…</span></span>
</div>

{{-- Filters --}}
<div class="filter-bar">
  <div class="filter-field">
    <label for="fDateFrom">From</label>
    <input type="date" id="fDateFrom">
  </div>
  <div class="filter-field">
    <label for="fDateTo">To</label>
    <input type="date" id="fDateTo">
  </div>
  <div class="filter-field">
    <label for="fPayment">Payment Method</label>
    <select id="fPayment">
      <option value="all">All Methods</option>
    </select>
  </div>
  <button class="filter-btn" id="applyFilters"><i class="fas fa-filter"></i> Apply</button>
  <button class="filter-btn-clear" id="clearFilters">Clear</button>
</div>

{{-- Summary Metrics --}}
<div class="metrics-grid">
  <div class="metric-card">
    <div class="metric-label">This Month Transactions</div>
    <div class="metric-value" id="mTxn">—</div>
    <div class="metric-sub">Completed sales</div>
  </div>
  <div class="metric-card">
    <div class="metric-label">This Month Revenue</div>
    <div class="metric-value" id="mRevenue">—</div>
    <div class="metric-sub">Total amount</div>
  </div>
  <div class="metric-card">
    <div class="metric-label">Items Sold (Month)</div>
    <div class="metric-value" id="mItems">—</div>
    <div class="metric-sub">Units sold</div>
  </div>
  <div class="metric-card">
    <div class="metric-label">Low Stock Alerts</div>
    <div class="metric-value" id="mLowStock">—</div>
    <div class="metric-sub">Need restocking</div>
  </div>
</div>

{{-- Main Tabs --}}
<div class="section-card">
  <div class="tab-bar">
    <button class="tab-btn active" data-tab="top">🏆 Top Products</button>
    <button class="tab-btn" data-tab="dayofweek">📅 Day of Week</button>
    <button class="tab-btn" data-tab="hourly">🕐 Peak Hours</button>
    <button class="tab-btn" data-tab="forecast">🔮 Next Month Forecast</button>
    <button class="tab-btn" data-tab="monthly">📈 Monthly Trend</button>
    <button class="tab-btn" data-tab="payment">💳 Payment Methods</button>
    <button class="tab-btn" data-tab="category">📦 Category Breakdown</button>
  </div>

  {{-- TAB: TOP PRODUCTS --}}
  <div id="tab-top" class="tab-pane active">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px" class="fc-grid-2">
      <div>
        <h3 class="pane-title">🔥 Top Products This Month</h3>
        <div class="chart-wrap"><canvas id="chartTopMonth"></canvas></div>
      </div>
      <div>
        <h3 class="pane-title">🏅 All-Time Top Products</h3>
        <div class="chart-wrap"><canvas id="chartTopAll"></canvas></div>
      </div>
    </div>
    <div id="topTable" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
  </div>

  {{-- TAB: DAY OF WEEK --}}
  <div id="tab-dayofweek" class="tab-pane">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px" class="fc-grid-2">
      <div>
        <h3 class="pane-title">📊 Transactions by Day</h3>
        <div class="chart-wrap"><canvas id="chartDow"></canvas></div>
      </div>
      <div>
        <h3 class="pane-title">🗓️ Revenue by Day</h3>
        <div class="chart-wrap"><canvas id="chartDowRev"></canvas></div>
      </div>
    </div>
    <h3 class="pane-title" style="margin-top:16px">🏖️ Weekend Top-Sellers (Sat &amp; Sun)</h3>
    <div id="weekendTable" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
  </div>

  {{-- TAB: PEAK HOURS --}}
  <div id="tab-hourly" class="tab-pane">
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px" class="fc-grid-2">
      <div>
        <h3 class="pane-title">🕐 Hourly Transactions</h3>
        <div class="chart-wrap"><canvas id="chartHourly"></canvas></div>
      </div>
      <div>
        <h3 class="pane-title">⚡ Peak Hours</h3>
        <div id="peakHoursDisplay" class="loading"><i class="fas fa-spinner"></i></div>
      </div>
    </div>
    <h3 class="pane-title" style="margin-top:16px">👷 Manpower Recommendations</h3>
    <div id="manpowerAdvice" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
  </div>

  {{-- TAB: NEXT MONTH FORECAST --}}
  <div id="tab-forecast" class="tab-pane">
    <div class="info-note">
      <i class="fas fa-info-circle"></i>
      <strong>How this works:</strong> Linear regression on the last 90 days of daily sales per product,
      scoped to the {{ $zone }} zone only.
      The trend arrow shows whether demand is growing, shrinking, or stable.
      Use the next-month forecast to plan restocking quantities.
    </div>
    <div id="forecastTable" class="loading"><i class="fas fa-spinner"></i><br>Calculating forecasts…</div>
  </div>

  {{-- TAB: MONTHLY TREND --}}
  <div id="tab-monthly" class="tab-pane">
    <h3 class="pane-title">📈 Monthly Revenue Trend (Last 12 Months)</h3>
    <div class="chart-wrap"><canvas id="chartMonthly"></canvas></div>
    <div id="monthlyTable" class="loading" style="margin-top:16px"><i class="fas fa-spinner"></i><br>Loading…</div>
  </div>

  {{-- TAB: PAYMENT METHODS --}}
  <div id="tab-payment" class="tab-pane">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px" class="fc-grid-2">
      <div>
        <h3 class="pane-title">💳 Revenue Share by Payment Method</h3>
        <div class="chart-wrap"><canvas id="chartPayment"></canvas></div>
      </div>
      <div>
        <h3 class="pane-title">📊 Breakdown</h3>
        <div id="paymentBars" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
      </div>
    </div>
  </div>

  {{-- TAB: CATEGORY BREAKDOWN --}}
  <div id="tab-category" class="tab-pane">
    <div class="info-note">
      <i class="fas fa-info-circle"></i>
      <strong>How this works:</strong> Sales are grouped by category type (e.g. Rides, Rentals, F&amp;B) and by
      individual category — never combined into one bucket. Percentages are share of total revenue for {{ $zone }}
      and always add up to 100%. <strong>Click any category row</strong> to see exactly which products generated
      its revenue.
    </div>
    <div id="categoryByType" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px" class="fc-grid-2">
      <div>
        <h3 class="pane-title">🏆 Best Sellers (Products)</h3>
        <div id="bestSellers" class="loading"><i class="fas fa-spinner"></i></div>
      </div>
      <div>
        <h3 class="pane-title">📉 Least Sellers (Products)</h3>
        <div id="worstSellers" class="loading"><i class="fas fa-spinner"></i></div>
      </div>
    </div>

    <h3 class="pane-title" style="margin-top:24px">📋 Full Category Breakdown <small style="font-weight:500;color:var(--fc-muted)">(click a row to expand)</small></h3>
    <div id="categoryTable" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
  </div>

</div>{{-- /section-card --}}

<div id="toast" class="toast"></div>

<style>
  @media(max-width:900px){ .fc-grid-2{ grid-template-columns:1fr !important; } }
</style>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

  /* ── CONFIG ─────────────────────────────────────────────────── */
  const ZONE    = @json($zone);
  const ML_API  = '{{ url("/api/zone-forecast") }}/' + encodeURIComponent(ZONE);
  const CSRF    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  /* ── HELPERS ────────────────────────────────────────────────── */
  function showToast(msg, type) {
      const t = document.getElementById('toast');
      t.textContent   = msg;
      t.className     = 'toast' + (type === 'error' ? ' error' : '');
      t.style.display = 'block';
      setTimeout(() => t.style.display = 'none', 3000);
  }

  function currentFilters() {
      const from = document.getElementById('fDateFrom').value;
      const to   = document.getElementById('fDateTo').value;
      const pay  = document.getElementById('fPayment').value;
      const params = new URLSearchParams();
      if (from) params.set('date_from', from);
      if (to)   params.set('date_to', to);
      if (pay && pay !== 'all') params.set('payment_method', pay);
      const qs = params.toString();
      return qs ? ('?' + qs) : '';
  }

  async function apiFetch(endpoint, withFilters = false) {
      const url = ML_API + endpoint + (withFilters ? currentFilters() : '');
      const r = await fetch(url, {
          headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
      });
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
  }

  function peso(n) {
      return '₱' + Number(n || 0).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
  }

  /* Indigo / coral chart palette, matched to the dashboard design system */
  const COLORS = [
      '#6C5CE0','#FF7A59','#22C3AE','#FFB020','#5445C4',
      '#FF9F80','#8B7CF6','#1FAE9E','#F0506E','#B9AFF8'
  ];

  function makeChart(id, type, labels, datasets, opts = {}) {
      const ctx = document.getElementById(id);
      if (!ctx) return;
      const existing = Chart.getChart(ctx);
      if (existing) existing.destroy();
      return new Chart(ctx, {
          type,
          data: { labels, datasets },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: opts.legend !== false, labels: { color: '#585B72', font: { family: 'Inter' }, usePointStyle: true, boxWidth: 8 } } },
              scales: type === 'bar' || type === 'line' ? {
                  y: { beginAtZero: true, grid: { color: '#ECEDF6' }, ticks: { color: '#9195AA' } },
                  x: { grid: { display: false }, ticks: { color: '#9195AA' } }
              } : undefined,
              ...opts.extra
          }
      });
  }

  /* ── TABS ───────────────────────────────────────────────────── */
  document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
          document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
          document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
          btn.classList.add('active');
          document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
      });
  });

  /* ── SUMMARY ────────────────────────────────────────────────── */
  async function loadSummary() {
      try {
          const d = await apiFetch('/summary', true);
          document.getElementById('mTxn').textContent      = Number(d.total_txn || 0).toLocaleString();
          document.getElementById('mRevenue').textContent  = peso(d.total_revenue);
          document.getElementById('mItems').textContent    = Number(d.total_items_sold || 0).toLocaleString();
          document.getElementById('mLowStock').textContent = d.low_stock_count;
      } catch(e) { console.error('Summary error', e); }
  }

  /* ── TOP PRODUCTS ───────────────────────────────────────────── */
  async function loadTopProducts() {
      const div = document.getElementById('topTable');
      try {
          const d = await apiFetch('/top_products', true);
          const tm = d.this_month.slice(0, 8);
          makeChart('chartTopMonth', 'bar',
              tm.map(r => r.product_name),
              [{ label: 'Units Sold', data: tm.map(r => r.total_qty), backgroundColor: COLORS, borderRadius: 8 }],
              { legend: false }
          );
          const at = d.all_time.slice(0, 8);
          makeChart('chartTopAll', 'bar',
              at.map(r => r.product_name),
              [{ label: 'Units Sold', data: at.map(r => r.total_qty), backgroundColor: COLORS.slice().reverse(), borderRadius: 8 }],
              { legend: false }
          );
          if (!d.this_month.length) {
              div.innerHTML = '<div class="error-msg">No sales data this month yet for ' + ZONE + '.</div>';
              return;
          }
          div.innerHTML = `
          <h3 class="pane-title" style="margin-top:16px">📋 This Month Details</h3>
          <div class="table-scroll">
          <table class="ml-table">
            <thead><tr><th>#</th><th>Product</th><th>Category</th><th>Units Sold</th><th>Revenue</th></tr></thead>
            <tbody>
              ${d.this_month.map((r,i) => `
              <tr>
                <td>${i+1}</td>
                <td><strong>${r.product_name}</strong></td>
                <td>${r.category_name}</td>
                <td>${Number(r.total_qty).toLocaleString()}</td>
                <td>${peso(r.total_revenue)}</td>
              </tr>`).join('')}
            </tbody>
          </table>
          </div>`;
      } catch(e) {
          div.innerHTML = `<div class="error-msg"><i class="fas fa-exclamation-triangle"></i> Could not load data. Is the ML server running?<br><small>${e}</small></div>`;
      }
  }

  /* ── DAY OF WEEK ────────────────────────────────────────────── */
  async function loadDayOfWeek() {
      const div = document.getElementById('weekendTable');
      try {
          const d = await apiFetch('/day_of_week');
          const days = d.by_day;
          makeChart('chartDow', 'bar',
              days.map(r => r.day_name),
              [{ label: 'Transactions', data: days.map(r => r.num_transactions),
                 backgroundColor: days.map(r => (r.day_name==='Saturday'||r.day_name==='Sunday') ? '#FF7A59' : '#6C5CE0'), borderRadius: 8 }],
              { legend: false }
          );
          makeChart('chartDowRev', 'bar',
              days.map(r => r.day_name),
              [{ label: 'Revenue', data: days.map(r => parseFloat(r.total_revenue || 0)),
                 backgroundColor: days.map(r => (r.day_name==='Saturday'||r.day_name==='Sunday') ? '#FFB020' : '#22C3AE'), borderRadius: 8 }],
              { legend: false }
          );
          if (!d.weekend_top.length) {
              div.innerHTML = '<div class="error-msg">No weekend sales data yet for ' + ZONE + '.</div>';
              return;
          }
          div.innerHTML = `
          <div class="table-scroll">
          <table class="ml-table">
            <thead><tr><th>#</th><th>Product</th><th>Category</th><th>Day</th><th>Units Sold</th></tr></thead>
            <tbody>
              ${d.weekend_top.map((r,i) => `
              <tr>
                <td>${i+1}</td>
                <td><strong>${r.product_name}</strong></td>
                <td>${r.category_name}</td>
                <td>${r.day_name}</td>
                <td>${Number(r.total_qty).toLocaleString()}</td>
              </tr>`).join('')}
            </tbody>
          </table>
          </div>`;
      } catch(e) {
          div.innerHTML = `<div class="error-msg">Could not load data. Is the ML server running?</div>`;
      }
  }

  /* ── PEAK HOURS ─────────────────────────────────────────────── */
  async function loadHourly() {
      const peakDiv = document.getElementById('peakHoursDisplay');
      const manpDiv = document.getElementById('manpowerAdvice');
      try {
          const d = await apiFetch('/hourly_peaks');
          makeChart('chartHourly', 'line',
              d.hourly.map(r => {
                  const h = parseInt(r.hour);
                  const s = h < 12 ? 'AM' : 'PM';
                  const disp = h === 0 ? 12 : h > 12 ? h-12 : h;
                  return disp + ':00 ' + s;
              }),
              [{ label: 'Transactions', data: d.hourly.map(r => r.num_transactions),
                 borderColor: '#6C5CE0', backgroundColor: 'rgba(108,92,224,.12)',
                 fill: true, tension: 0.4, pointRadius: 5, pointHoverRadius: 7, pointBackgroundColor: '#5445C4' }]
          );
          peakDiv.innerHTML = d.peak_hours.length
              ? '<p style="color:#585B72;margin-bottom:10px;font-size:.8rem">Hours with highest customer traffic:</p>' +
                d.peak_hours.map(h => `<span class="peak-badge"><i class="fas fa-fire"></i> ${h}</span>`).join('')
              : '<p style="color:#9195AA">Not enough data yet.</p>';
          if (!d.manpower_advice.length) {
              manpDiv.innerHTML = '<div class="error-msg">Not enough data for manpower suggestions.</div>';
              return;
          }
          manpDiv.innerHTML = d.manpower_advice.map(a => `
          <div class="staff-card">
            <div class="hour"><i class="fas fa-clock"></i> ${a.hour}
              &nbsp;·&nbsp; <span style="color:#1B1D28;font-size:.78rem;font-weight:500">${a.transactions} avg transactions</span>
            </div>
            <div class="note">👷 ${a.note}</div>
          </div>`).join('');
      } catch(e) {
          peakDiv.innerHTML = `<div class="error-msg">Could not load data.</div>`;
          manpDiv.innerHTML = `<div class="error-msg">Could not load data.</div>`;
      }
  }

  /* ── NEXT MONTH FORECAST ────────────────────────────────────── */
  async function loadForecast() {
      const div = document.getElementById('forecastTable');
      try {
          const d = await apiFetch('/next_month_forecast');
          document.getElementById('generatedAt').textContent = d.generated_at || '';
          if (!d.forecasts.length) {
              div.innerHTML = '<div class="error-msg">Not enough historical data for forecasting in ' + ZONE + '. Need at least 3 days of sales per product.</div>';
              return;
          }
          div.innerHTML = `
          <div class="table-scroll">
          <table class="ml-table">
            <thead><tr>
              <th>#</th><th>Product</th><th>Category</th>
              <th>Current Month Avg/Day</th><th>Forecast Daily Avg</th>
              <th>Forecast Next Month Total</th><th>Trend</th><th>Data Points</th>
            </tr></thead>
            <tbody>
              ${d.forecasts.map((r,i) => `
              <tr>
                <td>${i+1}</td>
                <td><strong>${r.product_name}</strong></td>
                <td>${r.category_name}</td>
                <td>${r.current_month_avg} units/day</td>
                <td>${r.forecast_daily_avg} units/day</td>
                <td><strong>${Number(r.forecast_next_month).toLocaleString()} units</strong></td>
                <td>
                  ${r.trend==='up'     ? '<span class="trend-up">▲ Growing</span>'    : ''}
                  ${r.trend==='down'   ? '<span class="trend-down">▼ Declining</span>' : ''}
                  ${r.trend==='stable' ? '<span class="trend-stable">→ Stable</span>'  : ''}
                </td>
                <td style="color:#9195AA;font-size:.72rem">${r.data_points} days</td>
              </tr>`).join('')}
            </tbody>
          </table>
          </div>`;
      } catch(e) {
          div.innerHTML = `<div class="error-msg"><i class="fas fa-exclamation-triangle"></i> Could not load forecast. Is the ML server running?<br><small>${e}</small></div>`;
      }
  }

  /* ── MONTHLY TREND ──────────────────────────────────────────── */
  async function loadMonthly() {
      const div = document.getElementById('monthlyTable');
      try {
          const d = await apiFetch('/monthly_trend');
          const rows = d.monthly;
          makeChart('chartMonthly', 'line',
              rows.map(r => r.month),
              [
                  { label: 'Revenue (₱)', data: rows.map(r => parseFloat(r.total_revenue || 0)),
                    borderColor: '#6C5CE0', backgroundColor: 'rgba(108,92,224,.10)',
                    fill: true, tension: 0.4, yAxisID: 'y' },
                  { label: 'Units Sold', data: rows.map(r => parseInt(r.total_qty || 0)),
                    borderColor: '#FF7A59', backgroundColor: 'rgba(255,122,89,.10)',
                    fill: true, tension: 0.4, yAxisID: 'y1' }
              ],
              { extra: { scales: {
                  y:  { type:'linear', position:'left',  beginAtZero:true, grid:{color:'#ECEDF6'}, ticks:{color:'#9195AA'} },
                  y1: { type:'linear', position:'right', beginAtZero:true, grid:{display:false}, ticks:{color:'#9195AA'} },
                  x:  { grid:{display:false}, ticks:{color:'#9195AA'} }
              }}}
          );
          if (!rows.length) {
              div.innerHTML = '<div class="error-msg">No monthly data yet for ' + ZONE + '.</div>';
              return;
          }
          div.innerHTML = `
          <h3 class="pane-title">📋 Monthly Breakdown</h3>
          <div class="table-scroll">
          <table class="ml-table">
            <thead><tr><th>Month</th><th>Transactions</th><th>Units Sold</th><th>Revenue</th></tr></thead>
            <tbody>
              ${rows.slice().reverse().map(r => `
              <tr>
                <td><strong>${r.month}</strong></td>
                <td>${Number(r.num_transactions).toLocaleString()}</td>
                <td>${Number(r.total_qty).toLocaleString()}</td>
                <td>${peso(r.total_revenue)}</td>
              </tr>`).join('')}
            </tbody>
          </table>
          </div>`;
      } catch(e) {
          div.innerHTML = `<div class="error-msg">Could not load monthly data.</div>`;
      }
  }

  /* ── PAYMENT METHODS ────────────────────────────────────────── */
  async function loadPaymentBreakdown() {
      const barsDiv = document.getElementById('paymentBars');
      try {
          const d = await apiFetch('/payment_breakdown', true);
          const methods = d.methods || [];

          const select = document.getElementById('fPayment');
          if (select.options.length <= 1) {
              methods.forEach(m => {
                  const opt = document.createElement('option');
                  opt.value = m.payment_method;
                  opt.textContent = m.payment_method;
                  select.appendChild(opt);
              });
          }

          if (!methods.length) {
              barsDiv.innerHTML = '<div class="error-msg">No payment data yet for ' + ZONE + '.</div>';
              makeChart('chartPayment', 'doughnut', [], []);
              return;
          }

          makeChart('chartPayment', 'doughnut',
              methods.map(m => m.payment_method),
              [{ data: methods.map(m => m.total_revenue), backgroundColor: COLORS, borderWidth: 0, hoverOffset: 6 }],
              { legend: true, extra: { cutout: '68%' } }
          );

          barsDiv.innerHTML = methods.map(m => `
            <div class="pct-row">
              <div class="pct-label">${m.payment_method}</div>
              <div class="pct-track"><div class="pct-fill" style="width:${m.percentage}%"><span>${peso(m.total_revenue)}</span></div></div>
              <div class="pct-value">${m.percentage}%</div>
            </div>`).join('');
      } catch(e) {
          barsDiv.innerHTML = `<div class="error-msg">Could not load payment data.</div>`;
      }
  }

  /* ── CATEGORY BREAKDOWN ─────────────────────────────────────── */
  async function loadCategoryBreakdown() {
      const byTypeDiv = document.getElementById('categoryByType');
      const bestDiv    = document.getElementById('bestSellers');
      const worstDiv   = document.getElementById('worstSellers');
      const tableDiv   = document.getElementById('categoryTable');
      try {
          const d = await apiFetch('/category_breakdown', true);
          const byType     = d.by_type || [];
          const byCategory = d.by_category || [];

          if (!byType.length) {
              byTypeDiv.innerHTML = '<div class="error-msg">No category sales data yet for ' + ZONE + '.</div>';
              bestDiv.innerHTML = '';
              worstDiv.innerHTML = '';
              tableDiv.innerHTML = '';
              return;
          }

          byTypeDiv.innerHTML = `
            <h3 class="pane-title">🗂️ By Category Type</h3>
            ${byType.map(t => `
              <div class="pct-row">
                <div class="pct-label">${t.category_type}</div>
                <div class="pct-track"><div class="pct-fill" style="width:${t.percentage}%"><span>${peso(t.total_revenue)}</span></div></div>
                <div class="pct-value">${t.percentage}%</div>
              </div>`).join('')}`;

          bestDiv.innerHTML = (d.best_sellers || []).map((r,i) => `
            <div class="staff-card">
              <div class="hour">#${i+1} · ${r.product_name} <span class="best-tag">${r.category_name}</span></div>
              <div class="note">${Number(r.total_qty).toLocaleString()} units · ${peso(r.total_revenue)} · ${r.percentage}% of total revenue</div>
            </div>`).join('') || '<p style="color:#9195AA">Not enough data yet.</p>';

          worstDiv.innerHTML = (d.worst_sellers || []).map((r,i) => `
            <div class="staff-card" style="border-left-color:#F0506E">
              <div class="hour">#${i+1} · ${r.product_name} <span class="worst-tag">${r.category_name}</span></div>
              <div class="note">${Number(r.total_qty).toLocaleString()} units · ${peso(r.total_revenue)} · ${r.percentage}% of total revenue</div>
            </div>`).join('') || '<p style="color:#9195AA">Not enough data yet.</p>';

          tableDiv.innerHTML = `
            <div class="table-scroll">
            <table class="ml-table" id="catBreakdownTable">
              <thead><tr><th></th><th>#</th><th>Category</th><th>Type</th><th>Units Sold</th><th>Revenue</th><th>% of Total</th></tr></thead>
              <tbody>
                ${byCategory.map((r,i) => `
                <tr class="cat-row" data-cat="cat-${i}" style="cursor:pointer">
                  <td><i class="fas fa-chevron-right" id="caret-${i}"></i></td>
                  <td>${i+1}</td>
                  <td><strong>${r.category_name}</strong></td>
                  <td>${r.category_type}</td>
                  <td>${Number(r.total_qty).toLocaleString()}</td>
                  <td>${peso(r.total_revenue)}</td>
                  <td><strong>${r.percentage}%</strong></td>
                </tr>
                <tr id="cat-${i}" style="display:none">
                  <td colspan="7" style="background:var(--fc-primary-light);padding:0">
                    ${(r.products && r.products.length) ? `
                      <table class="ml-table" style="margin:8px 12px;width:calc(100% - 24px)">
                        <thead><tr><th>Product</th><th>Units Sold</th><th>Revenue</th><th>% of ${r.category_name} Revenue</th></tr></thead>
                        <tbody>
                          ${r.products.map(p => `
                          <tr>
                            <td>${p.product_name}</td>
                            <td>${Number(p.total_qty).toLocaleString()}</td>
                            <td>${peso(p.total_revenue)}</td>
                            <td>${p.percentage_of_category}%</td>
                          </tr>`).join('')}
                        </tbody>
                      </table>` : '<p style="color:#9195AA;padding:10px 16px">No product-level data.</p>'}
                  </td>
                </tr>`).join('')}
              </tbody>
            </table>
            </div>`;

          document.querySelectorAll('.cat-row').forEach(row => {
              row.addEventListener('click', () => {
                  const target = document.getElementById(row.dataset.cat);
                  const caret  = row.querySelector('i');
                  const isOpen = target.style.display !== 'none';
                  target.style.display = isOpen ? 'none' : 'table-row';
                  caret.classList.toggle('fa-chevron-right', isOpen);
                  caret.classList.toggle('fa-chevron-down', !isOpen);
              });
          });
      } catch(e) {
          byTypeDiv.innerHTML = `<div class="error-msg">Could not load category data.</div>`;
      }
  }

  /* ── FILTER CONTROLS ────────────────────────────────────────── */
  function reloadFilteredData() {
      loadSummary();
      loadTopProducts();
      loadPaymentBreakdown();
      loadCategoryBreakdown();
  }

  document.getElementById('applyFilters').addEventListener('click', reloadFilteredData);
  document.getElementById('clearFilters').addEventListener('click', () => {
      document.getElementById('fDateFrom').value = '';
      document.getElementById('fDateTo').value = '';
      document.getElementById('fPayment').value = 'all';
      reloadFilteredData();
  });

  /* ── BOOT ───────────────────────────────────────────────────── */
  loadSummary();
  loadTopProducts();
  loadDayOfWeek();
  loadHourly();
  loadForecast();
  loadMonthly();
  loadPaymentBreakdown();
  loadCategoryBreakdown();
</script>
@endpush