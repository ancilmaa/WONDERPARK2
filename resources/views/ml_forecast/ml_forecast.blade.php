@extends('layouts.sidebar')

@section('title', 'Sales Forecast')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
@endpush

@section('styles')
<style>


  /* ============================================================
     DESIGN TOKENS — indigo / coral dashboard system
     Shared across Zone Forecast, Store Forecast & Inventory Forecast
     ============================================================ */
  :root{
    --fc-bg:        #F5F6FB;
    --fc-card:      #FFFFFF;
    --fc-dark:      #1E1B3A;
    --fc-ink:       #1B1D28;
    --fc-ink-soft:  #33344A;
    --fc-muted:     #5B5D72;
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
    --fc-radius-lg: 18px;
    --fc-radius-md: 12px;
    --fc-radius-sm: 9px;
  }

  .content-body{padding:clamp(14px, 2.4vw, 28px);max-width:1440px;margin:0 auto;background:var(--fc-bg);}
  .jak{ font-family:'Plus Jakarta Sans','Inter',sans-serif; }
  * { box-sizing:border-box; }

  /* ===== HEADER ROW: title + live controls, same row to reclaim vertical space ===== */
  .dash-header{
    display:flex; justify-content:space-between; align-items:flex-start; gap:16px;
    flex-wrap:wrap; margin-bottom:12px;
  }
  .dash-header .eyebrow{ font-size:.68rem; font-weight:700; color:var(--fc-primary); text-transform:uppercase; letter-spacing:.09em; margin-bottom:4px; }
  .dash-header h2{
    font-family:'Plus Jakarta Sans',sans-serif; font-size:1.32rem; font-weight:800; color:var(--fc-ink);
    display:flex; align-items:center; gap:9px; letter-spacing:-.01em; margin:0;
  }
  .dash-header h2 i{
    width:32px;height:32px;border-radius:10px;background:var(--fc-primary-light);color:var(--fc-primary);
    display:inline-flex;align-items:center;justify-content:center;font-size:.86rem;
  }
  .dash-header p{ color:var(--fc-muted); font-size:.78rem; margin:3px 0 0; }
  .live-chip{
    display:inline-flex;align-items:center;gap:7px;background:var(--fc-primary-light);
    color:var(--fc-primary-dark);padding:6px 13px;border-radius:999px;font-size:.72rem;font-weight:700;white-space:nowrap;
  }
  .live-chip::before{content:'';width:6px;height:6px;border-radius:50%;background:var(--fc-teal);box-shadow:0 0 0 3px var(--fc-teal-bg);}

  /* ===== INLINE FILTER BAR — one compact row, not a stacked block ===== */
  .filter-bar{
    display:flex;align-items:center;gap:9px;flex-wrap:wrap;
    background:var(--fc-card);border:1px solid var(--fc-line);border-radius:var(--fc-radius-md);
    padding:9px 12px;margin-bottom:12px;box-shadow:var(--fc-shadow-sm);
  }
  .filter-bar .live-chip{ margin-right:4px; }
  .filter-field{display:flex;align-items:center;gap:6px;}
  .filter-field label{font-size:.66rem;font-weight:700;color:var(--fc-muted);text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;}
  .filter-field input, .filter-field select{
    border:1px solid var(--fc-line);border-radius:var(--fc-radius-sm);padding:6px 9px;
    font-size:.78rem;font-family:inherit;color:var(--fc-ink);background:var(--fc-bg);
  }
  .filter-field input{width:130px;}
  .filter-field input:focus, .filter-field select:focus{outline:none;border-color:var(--fc-primary);box-shadow:0 0 0 3px var(--fc-primary-light);}
  .filter-spacer{ flex:1 1 auto; }
  .filter-btn{
    background:var(--fc-primary);color:#fff;border:none;border-radius:var(--fc-radius-sm);padding:7px 15px;
    font-weight:700;font-size:.78rem;cursor:pointer;display:flex;align-items:center;gap:6px;transition:.15s;
  }
  .filter-btn:hover{background:var(--fc-primary-dark);}
  .filter-btn-clear{
    background:var(--fc-bg);color:var(--fc-ink-soft);border:1px solid var(--fc-line);
    border-radius:var(--fc-radius-sm);padding:7px 13px;font-weight:700;font-size:.78rem;cursor:pointer;
  }
  .filter-btn-clear:hover{border-color:var(--fc-primary);color:var(--fc-primary-dark);}

  /* ===== KPI STRIP — compact, icon + numbers side by side ===== */
  .kpi-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:12px;}
  @media(max-width:980px){ .kpi-strip{grid-template-columns:repeat(2,1fr);} }
  @media(max-width:520px){ .kpi-strip{grid-template-columns:1fr;} }

  .kpi-card{
    background:var(--fc-card);border-radius:var(--fc-radius-md);padding:12px 14px;
    box-shadow:var(--fc-shadow-sm);display:flex;align-items:center;gap:11px;
    border-left:3px solid var(--fc-primary);
  }
  .kpi-card.is-revenue{border-left-color:var(--fc-accent);}
  .kpi-card.is-items{border-left-color:var(--fc-teal);}
  .kpi-card.is-alert{border-left-color:var(--fc-red);}
  .kpi-body{ min-width:0; }
  .kpi-label{font-size:.63rem;color:var(--fc-muted);font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;}
  .kpi-value{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.28rem;font-weight:800;color:var(--fc-ink);line-height:1.1;letter-spacing:-.01em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .kpi-sub{font-size:10.5px;color:var(--fc-muted);margin-top:1px;font-weight:600;}

  /* ===== DASHBOARD BODY — sidebar nav + content panel, side by side ===== */
  .dash-body{ display:grid; grid-template-columns:196px 1fr; gap:14px; align-items:start; }
  @media(max-width:900px){ .dash-body{ grid-template-columns:1fr; } }

  .side-nav{
    background:var(--fc-card); border-radius:var(--fc-radius-lg); box-shadow:var(--fc-shadow-sm);
    padding:8px; display:flex; flex-direction:column; gap:2px; position:sticky; top:12px;
  }
  @media(max-width:900px){
    .side-nav{ position:static; flex-direction:row; overflow-x:auto; scrollbar-width:none; }
    .side-nav::-webkit-scrollbar{ display:none; }
  }
  .tab-btn{
    all:unset; box-sizing:border-box; cursor:pointer; display:flex; align-items:center; gap:9px;
    padding:9px 11px; border-radius:var(--fc-radius-sm); font-size:.79rem; font-weight:600;
    color:var(--fc-ink-soft); font-family:'Inter',sans-serif; transition:.12s; white-space:nowrap;
  }
  .tab-btn i{ width:16px; text-align:center; color:var(--fc-muted); font-size:.82rem; }
  .tab-btn.active{ background:var(--fc-primary-light); color:var(--fc-primary-dark); }
  .tab-btn.active i{ color:var(--fc-primary); }
  .tab-btn:hover:not(.active){ background:var(--fc-bg); }

  .content-panel{ background:var(--fc-card); border-radius:var(--fc-radius-lg); box-shadow:var(--fc-shadow-sm); overflow:hidden; }
  .tab-pane{display:none; padding:18px 20px;}
  .tab-pane.active{display:block;animation:fcFade .2s ease;}
  @keyframes fcFade{from{opacity:0;transform:translateY(3px)}to{opacity:1;transform:translateY(0)}}

  .pane-title{margin:0 0 10px;font-family:'Plus Jakarta Sans',sans-serif;font-size:.92rem;font-weight:700;color:var(--fc-ink);display:flex;align-items:center;gap:7px;}
  .pane-title i{ color:var(--fc-primary); font-size:.82rem; }

  .fc-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
  @media(max-width:900px){ .fc-grid-2{ grid-template-columns:1fr !important; } }

  .chart-wrap{position:relative;height:240px;margin-bottom:12px;}

  .info-note{
    background:var(--fc-primary-light);border:1px solid #DED6FA;border-radius:var(--fc-radius-sm);
    padding:9px 13px;margin-bottom:14px;font-size:.76rem;color:var(--fc-ink-soft);
    display:flex; align-items:flex-start; gap:8px; line-height:1.45;
  }
  .info-note i{ color:var(--fc-primary); margin-top:2px; flex:0 0 auto; }
  .info-note strong{color:var(--fc-primary-dark);}

  /* ===== TABLES — tighter rows ===== */
  .ml-table{width:100%;border-collapse:collapse;font-size:.79rem;}
  .ml-table th{background:var(--fc-dark);color:#fff;padding:9px 12px;text-align:left;font-weight:600;font-size:.68rem;text-transform:uppercase;letter-spacing:.03em;border-bottom:none;}
  .ml-table td{padding:8px 12px;border-bottom:1px solid var(--fc-line);color:var(--fc-ink-soft);}
  .ml-table tr:nth-child(even) td{ background:#FBFBFD; }
  .ml-table tr:last-child td{border-bottom:none;}
  .ml-table tr:hover td{background:var(--fc-primary-light) !important;}
  .table-scroll{width:100%;overflow-x:auto;}

  /* ===== PERCENTAGE BARS ===== */
  .pct-row{display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--fc-line);flex-wrap:wrap;}
  .pct-row:last-child{border-bottom:none;}
  .pct-label{width:150px;flex-shrink:0;font-size:.79rem;font-weight:700;color:var(--fc-ink);}
  .pct-track{flex:1;min-width:100px;height:17px;background:var(--fc-primary-light);border-radius:20px;overflow:hidden;position:relative;}
  .pct-fill{height:100%;border-radius:20px;background:linear-gradient(90deg,var(--fc-primary),var(--fc-accent));display:flex;align-items:center;justify-content:flex-end;padding-right:8px;transition:width .5s ease;}
  .pct-fill span{color:#fff;font-size:.65rem;font-weight:700;white-space:nowrap;}
  .pct-value{width:50px;flex-shrink:0;text-align:right;font-size:.79rem;font-weight:800;color:var(--fc-primary-dark);}
  .best-tag{background:var(--fc-teal-bg);color:var(--fc-teal);padding:2px 9px;border-radius:20px;font-size:.63rem;font-weight:700;margin-left:7px;}
  .worst-tag{background:var(--fc-red-bg);color:var(--fc-red);padding:2px 9px;border-radius:20px;font-size:.63rem;font-weight:700;margin-left:7px;}

  /* ===== BADGES ===== */
  .trend-up{background:var(--fc-teal-bg);color:var(--fc-teal);padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:700;}
  .trend-down{background:var(--fc-red-bg);color:var(--fc-red);padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:700;}
  .trend-stable{background:var(--fc-amber-bg);color:#946600;padding:3px 10px;border-radius:20px;font-size:.7rem;font-weight:700;}
  .peak-badge{background:var(--fc-primary);color:#fff;padding:5px 12px;border-radius:20px;font-size:.7rem;font-weight:700;display:inline-block;margin:0 6px 6px 0;}

  /* Compact horizontal manpower chips instead of tall stacked cards */
  .staff-grid{ display:grid; grid-template-columns:repeat(auto-fill, minmax(215px,1fr)); gap:8px; }
  .staff-card{background:var(--fc-primary-light);border-left:3px solid var(--fc-primary);padding:9px 12px;border-radius:var(--fc-radius-sm);}
  .staff-card .hour{font-weight:700;color:var(--fc-primary-dark);font-size:.81rem;}
  .staff-card .note{color:var(--fc-ink-soft);font-size:.72rem;margin-top:3px;line-height:1.35;}

  /* ===== LOADING / ERROR ===== */
  .loading{text-align:center;padding:28px;color:var(--fc-muted);}
  .loading i{font-size:22px;animation:spin 1s linear infinite;color:var(--fc-primary);}
  @keyframes spin{to{transform:rotate(360deg)}}
  .error-msg{background:var(--fc-red-bg);color:var(--fc-red);padding:11px;border-radius:var(--fc-radius-sm);text-align:center;font-size:.79rem;}

  /* ===== TOAST ===== */
  .toast{position:fixed;bottom:18px;right:18px;background:var(--fc-dark);color:#fff;padding:11px 16px;border-radius:var(--fc-radius-sm);display:none;box-shadow:var(--fc-shadow-md);z-index:3000;font-weight:700;font-size:.79rem;}
  .toast.error{background:var(--fc-red);}

  /* ===== RESPONSIVE ===== */
  @media(max-width:900px){ .content-body{padding:14px;} }
  @media(max-width:640px){ .tab-pane{padding:14px;} .chart-wrap{height:200px;} .pct-label{width:110px;} }
</style>
@endsection

@section('content')

<div class="dash-header">
  <div>
    <div class="eyebrow">WonderPark Lipa &middot; Forecasting</div>
    <h2>Sales Forecast</h2>
    <p>ML-powered sales trends and demand predictions for WonderPark Lipa</p>
  </div>
</div>

{{-- Filters + live status, single compact row --}}
<div class="filter-bar">
  <span class="live-chip">Live · <span id="generatedAt">Loading…</span></span>
  <div class="filter-field">
    <label for="fDateFrom">From</label>
    <input type="date" id="fDateFrom">
  </div>
  <div class="filter-field">
    <label for="fDateTo">To</label>
    <input type="date" id="fDateTo">
  </div>
  <div class="filter-field">
    <label for="fPayment">Payment</label>
    <select id="fPayment">
      <option value="all">All Methods</option>
    </select>
  </div>
  <div class="filter-spacer"></div>
  <button class="filter-btn" id="applyFilters">Apply</button>
  <button class="filter-btn-clear" id="clearFilters">Clear</button>
</div>

{{-- KPI strip --}}
<div class="kpi-strip">
  <div class="kpi-card">
    <div class="kpi-body">
      <div class="kpi-label">Transactions</div>
      <div class="kpi-value" id="mTxn">—</div>
      <div class="kpi-sub">This month, completed</div>
    </div>
  </div>
  <div class="kpi-card is-revenue">
    <div class="kpi-body">
      <div class="kpi-label">Revenue</div>
      <div class="kpi-value" id="mRevenue">—</div>
      <div class="kpi-sub">This month total</div>
    </div>
  </div>
  <div class="kpi-card is-items">
    <div class="kpi-body">
      <div class="kpi-label">Items Sold</div>
      <div class="kpi-value" id="mItems">—</div>
      <div class="kpi-sub">Units this month</div>
    </div>
  </div>
  <div class="kpi-card is-alert">
    <div class="kpi-body">
      <div class="kpi-label">Low Stock</div>
      <div class="kpi-value" id="mLowStock">—</div>
      <div class="kpi-sub">Need restocking</div>
    </div>
  </div>
</div>

{{-- Sidebar nav + content panel --}}
<div class="dash-body">
  <div class="side-nav">
    <button class="tab-btn active" data-tab="top"><i class="fas fa-trophy"></i> Top Products</button>
    <button class="tab-btn" data-tab="dayofweek"><i class="fas fa-calendar-week"></i> Day of Week</button>
    <button class="tab-btn" data-tab="hourly"><i class="fas fa-clock"></i> Peak Hours</button>
    <button class="tab-btn" data-tab="forecast"><i class="fas fa-wand-magic-sparkles"></i> Next Month</button>
    <button class="tab-btn" data-tab="monthly"><i class="fas fa-chart-line"></i> Monthly Trend</button>
    <button class="tab-btn" data-tab="payment"><i class="fas fa-credit-card"></i> Payment</button>
    <button class="tab-btn" data-tab="category"><i class="fas fa-boxes-stacked"></i> Categories</button>
  </div>

  <div class="content-panel">

    {{-- TAB: TOP PRODUCTS --}}
    <div id="tab-top" class="tab-pane active">
      <div class="fc-grid-2">
        <div>
          <h3 class="pane-title">Top Products This Month</h3>
          <div class="chart-wrap"><canvas id="chartTopMonth"></canvas></div>
        </div>
        <div>
          <h3 class="pane-title">All-Time Top Products</h3>
          <div class="chart-wrap"><canvas id="chartTopAll"></canvas></div>
        </div>
      </div>
      <div id="topTable" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
    </div>

    {{-- TAB: DAY OF WEEK --}}
    <div id="tab-dayofweek" class="tab-pane">
      <div class="fc-grid-2">
        <div>
          <h3 class="pane-title">Transactions by Day</h3>
          <div class="chart-wrap"><canvas id="chartDow"></canvas></div>
        </div>
        <div>
          <h3 class="pane-title">Revenue by Day</h3>
          <div class="chart-wrap"><canvas id="chartDowRev"></canvas></div>
        </div>
      </div>
      <h3 class="pane-title">Weekend Top-Sellers (Sat &amp; Sun)</h3>
      <div id="weekendTable" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
    </div>

    {{-- TAB: PEAK HOURS --}}
    <div id="tab-hourly" class="tab-pane">
      <div style="display:grid;grid-template-columns:1.6fr 1fr;gap:16px" class="fc-grid-2">
        <div>
          <h3 class="pane-title">Hourly Transactions</h3>
          <div class="chart-wrap"><canvas id="chartHourly"></canvas></div>
        </div>
        <div>
          <h3 class="pane-title">Peak Hours</h3>
          <div id="peakHoursDisplay" class="loading"><i class="fas fa-spinner"></i></div>
        </div>
      </div>
      <h3 class="pane-title">Manpower Recommendations</h3>
      <div id="manpowerAdvice" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
    </div>

    {{-- TAB: NEXT MONTH FORECAST --}}
    <div id="tab-forecast" class="tab-pane">
      <div class="info-note">
        <span><strong>How this works:</strong> Linear regression on the last 90 days of daily sales per product. The trend arrow shows whether demand is growing, shrinking, or stable &mdash; use the next-month forecast to plan restocking quantities.</span>
      </div>
      <div id="forecastTable" class="loading"><i class="fas fa-spinner"></i><br>Calculating forecasts…</div>
    </div>

    {{-- TAB: MONTHLY TREND --}}
    <div id="tab-monthly" class="tab-pane">
      <h3 class="pane-title">Monthly Revenue Trend (Last 12 Months)</h3>
      <div class="chart-wrap"><canvas id="chartMonthly"></canvas></div>
      <div id="monthlyTable" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
    </div>

    {{-- TAB: PAYMENT METHODS --}}
    <div id="tab-payment" class="tab-pane">
      <div class="fc-grid-2">
        <div>
          <h3 class="pane-title">Revenue Share by Payment Method</h3>
          <div class="chart-wrap"><canvas id="chartPayment"></canvas></div>
        </div>
        <div>
          <h3 class="pane-title">Breakdown</h3>
          <div id="paymentBars" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
        </div>
      </div>
    </div>

    {{-- TAB: CATEGORY BREAKDOWN --}}
    <div id="tab-category" class="tab-pane">
      <div class="info-note">
        <span><strong>How this works:</strong> Sales are grouped by category type (e.g. Rides, Rentals, F&amp;B) and by individual category &mdash; never combined into one bucket. Percentages are share of total store-wide revenue and always add up to 100%. <strong>Click any category row</strong> to see exactly which products generated its revenue.</span>
      </div>
      <div id="categoryByType" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>

      <div class="fc-grid-2" style="margin-top:18px">
        <div>
          <h3 class="pane-title">Best Sellers (Products)</h3>
          <div id="bestSellers" class="loading"><i class="fas fa-spinner"></i></div>
        </div>
        <div>
          <h3 class="pane-title">Least Sellers (Products)</h3>
          <div id="worstSellers" class="loading"><i class="fas fa-spinner"></i></div>
        </div>
      </div>

      <h3 class="pane-title" style="margin-top:18px">Full Category Breakdown <small style="font-weight:500;color:var(--fc-muted)">&nbsp;(click a row to expand)</small></h3>
      <div id="categoryTable" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
    </div>

  </div>{{-- /content-panel --}}
</div>{{-- /dash-body --}}

<div id="toast" class="toast"></div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

  /* ── CONFIG ─────────────────────────────────────────────────── */
  const ML_API  = '{{ url("/api/ml") }}';
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
      '#A8B8F0','#F5B8A8','#9DDBC4','#F5D98A','#C9A8E8',
      '#F0A8C4','#8FCCE8','#C4E0A0','#F0C48F','#B8A8D9'
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
              plugins: { legend: { display: opts.legend !== false, labels: { color: '#1B1D28', font: { family: 'Inter' }, usePointStyle: true, boxWidth: 8 } } },
              scales: type === 'bar' || type === 'line' ? {
                  y: { beginAtZero: true, grid: { color: '#ECEDF6' }, ticks: { color: '#1B1D28' } },
                  x: { grid: { display: false }, ticks: { color: '#1B1D28' } }
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
              [{ label: 'Units Sold', data: tm.map(r => r.total_qty), backgroundColor: COLORS, borderRadius: 6 }],
              { legend: false }
          );
          const at = d.all_time.slice(0, 8);
          makeChart('chartTopAll', 'bar',
              at.map(r => r.product_name),
              [{ label: 'Units Sold', data: at.map(r => r.total_qty), backgroundColor: COLORS.slice().reverse(), borderRadius: 6 }],
              { legend: false }
          );
          if (!d.this_month.length) {
              div.innerHTML = '<div class="error-msg">No sales data this month yet.</div>';
              return;
          }
          div.innerHTML = `
          <h3 class="pane-title">This Month Details</h3>
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
          div.innerHTML = `<div class="error-msg">Could not load data. Is the ML server running?<br><small>${e}</small></div>`;
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
                 backgroundColor: days.map(r => (r.day_name==='Saturday'||r.day_name==='Sunday') ? '#F5B8A8' : '#A8B8F0'), borderRadius: 6 }],
              { legend: false }
          );
          makeChart('chartDowRev', 'bar',
              days.map(r => r.day_name),
              [{ label: 'Revenue', data: days.map(r => parseFloat(r.total_revenue || 0)),
                 backgroundColor: days.map(r => (r.day_name==='Saturday'||r.day_name==='Sunday') ? '#F5D98A' : '#9DDBC4'), borderRadius: 6 }],
              { legend: false }
          );
          if (!d.weekend_top.length) {
              div.innerHTML = '<div class="error-msg">No weekend sales data yet.</div>';
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
                 borderColor: '#8FA3E8', backgroundColor: 'rgba(168,184,240,.28)',
                 fill: true, tension: 0.4, pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#6B85DE' }]
          );
          peakDiv.innerHTML = d.peak_hours.length
              ? '<p style="color:#33344A;margin-bottom:8px;font-size:.78rem">Hours with highest customer traffic:</p>' +
                d.peak_hours.map(h => `<span class="peak-badge">${h}</span>`).join('')
              : '<p style="color:#5B5D72">Not enough data yet.</p>';
          if (!d.manpower_advice.length) {
              manpDiv.innerHTML = '<div class="error-msg">Not enough data for manpower suggestions.</div>';
              return;
          }
          manpDiv.innerHTML = '<div class="staff-grid">' + d.manpower_advice.map(a => `
          <div class="staff-card">
            <div class="hour">${a.hour}
              &nbsp;·&nbsp; <span style="color:#1B1D28;font-size:.72rem;font-weight:500">${a.transactions} avg txns</span>
            </div>
            <div class="note">${a.note}</div>
          </div>`).join('') + '</div>';
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
              div.innerHTML = '<div class="error-msg">Not enough historical data for forecasting. Need at least 3 days of sales per product.</div>';
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
                <td style="color:#5B5D72;font-size:.7rem">${r.data_points} days</td>
              </tr>`).join('')}
            </tbody>
          </table>
          </div>`;
      } catch(e) {
          div.innerHTML = `<div class="error-msg">Could not load forecast. Is the ML server running?<br><small>${e}</small></div>`;
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
                    borderColor: '#8FA3E8', backgroundColor: 'rgba(168,184,240,.25)',
                    fill: true, tension: 0.4, yAxisID: 'y' },
                  { label: 'Units Sold', data: rows.map(r => parseInt(r.total_qty || 0)),
                    borderColor: '#E89A85', backgroundColor: 'rgba(245,184,168,.25)',
                    fill: true, tension: 0.4, yAxisID: 'y1' }
              ],
              { extra: { scales: {
                  y:  { type:'linear', position:'left',  beginAtZero:true, grid:{color:'#ECEDF6'}, ticks:{color:'#1B1D28'} },
                  y1: { type:'linear', position:'right', beginAtZero:true, grid:{display:false}, ticks:{color:'#1B1D28'} },
                  x:  { grid:{display:false}, ticks:{color:'#1B1D28'} }
              }}}
          );
          if (!rows.length) {
              div.innerHTML = '<div class="error-msg">No monthly data yet.</div>';
              return;
          }
          div.innerHTML = `
          <h3 class="pane-title">Monthly Breakdown</h3>
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
              barsDiv.innerHTML = '<div class="error-msg">No payment data yet.</div>';
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
              byTypeDiv.innerHTML = '<div class="error-msg">No category sales data yet.</div>';
              bestDiv.innerHTML = '';
              worstDiv.innerHTML = '';
              tableDiv.innerHTML = '';
              return;
          }

          byTypeDiv.innerHTML = `
            <h3 class="pane-title">By Category Type</h3>
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
            </div>`).join('') || '<p style="color:#5B5D72">Not enough data yet.</p>';

          worstDiv.innerHTML = (d.worst_sellers || []).map((r,i) => `
            <div class="staff-card" style="border-left-color:#F0506E">
              <div class="hour">#${i+1} · ${r.product_name} <span class="worst-tag">${r.category_name}</span></div>
              <div class="note">${Number(r.total_qty).toLocaleString()} units · ${peso(r.total_revenue)} · ${r.percentage}% of total revenue</div>
            </div>`).join('') || '<p style="color:#5B5D72">Not enough data yet.</p>';

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
                      <table class="ml-table" style="margin:6px 10px;width:calc(100% - 20px)">
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
                      </table>` : '<p style="color:#5B5D72;padding:10px 14px">No product-level data.</p>'}
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