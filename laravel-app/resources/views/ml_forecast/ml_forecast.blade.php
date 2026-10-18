@extends('layouts.sidebar')

@php
    $zoneIcons = [
        'Skataya'      => 'fa-bolt',
        'Piyestarides' => 'fa-flag-checkered',
        'Dinolakbay'   => 'fa-dragon',
    ];

    // Same URLs as the sidebar submenu
    $zoneUrls = [
        'Skataya'      => url('/RollerFever'),
        'Piyestarides' => url('/fieldOfRides'),
        'Dinolakbay'   => url('/DinoAdventure'),
    ];
@endphp

@section('title', 'Sales Forecast')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
@endpush

@section('styles')
<style>
  :root{
    --fc-bg:#F8FAFC; --fc-card:#fff; --fc-line:#E5E7EF;
    --fc-ink:#1B1D28; --fc-ink-soft:#33344A; --fc-muted:#5B5D72;
    --fc-primary:#4F46E5; --fc-primary-dark:#4338CA; --fc-primary-light:#EEF0FF;
    --fc-teal:#0F766E; --fc-teal-bg:#DDF7F2;
    --fc-amber:#92400E; --fc-amber-bg:#FEF3C7;
    --fc-red:#BE123C; --fc-red-bg:#FFE4EA;
    --fc-radius:14px; --fc-shadow:0 1px 2px rgba(15,23,42,.04);
  }

  /* header */
  .fc-head{ padding:4px 0 18px; }
  .fc-head h1{ font-family:'Plus Jakarta Sans',sans-serif; font-size:1.5rem; font-weight:800; color:var(--fc-ink); margin:0 0 4px; letter-spacing:-.02em; }
  .fc-head p{ margin:0; font-size:.82rem; color:var(--fc-muted); }
  .fc-live{ display:inline-flex; align-items:center; gap:7px; margin-top:8px; font-size:.72rem; font-weight:600; color:var(--fc-muted); }
  .fc-live::before{ content:''; width:7px; height:7px; border-radius:50%; background:#14B8A6; box-shadow:0 0 0 3px var(--fc-teal-bg); }

  /* zone cards */
  .zone-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:16px; }
  @media(max-width:980px){ .zone-grid{ grid-template-columns:1fr; } }

  .zone-card{
    display:block; text-decoration:none; color:inherit;
    background:var(--fc-card); border:1px solid var(--fc-line); border-radius:var(--fc-radius);
    padding:18px; box-shadow:var(--fc-shadow);
    transition:transform .15s, border-color .15s, box-shadow .15s;
  }
  .zone-card:hover{ transform:translateY(-2px); border-color:var(--fc-primary); box-shadow:0 8px 22px rgba(79,70,229,.12); }
  .zone-card:focus-visible{ outline:2px solid var(--fc-primary); outline-offset:3px; }

  .zone-top{ display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:14px; }
  .zone-name{ display:flex; align-items:center; gap:10px; font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:1rem; color:var(--fc-ink); }
  .zone-name i{ width:36px; height:36px; border-radius:10px; background:var(--fc-primary-light); color:var(--fc-primary-dark); display:inline-flex; align-items:center; justify-content:center; font-size:.9rem; }
  .zone-go{ font-size:.72rem; font-weight:700; color:var(--fc-primary-dark); display:inline-flex; align-items:center; gap:6px; }

  .zone-label{ font-size:.66rem; font-weight:700; color:var(--fc-muted); text-transform:uppercase; letter-spacing:.06em; }
  .zone-gross{ font-family:'Plus Jakarta Sans',sans-serif; font-size:1.7rem; font-weight:800; color:var(--fc-ink); line-height:1.2; margin:2px 0 8px; }
  .zone-meta{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; font-size:.74rem; color:var(--fc-muted); }

  .trend-up, .trend-down, .trend-stable{ padding:3px 10px; border-radius:20px; font-size:.72rem; font-weight:700; white-space:nowrap; }
  .trend-up{ background:var(--fc-teal-bg); color:var(--fc-teal); }
  .trend-down{ background:var(--fc-red-bg); color:var(--fc-red); }
  .trend-stable{ background:var(--fc-amber-bg); color:var(--fc-amber); }

  .spark-wrap{ position:relative; height:70px; margin-top:14px; }

  /* comparison panel */
  .content-panel{ background:var(--fc-card); border:1px solid var(--fc-line); border-radius:var(--fc-radius); box-shadow:var(--fc-shadow); padding:22px; }
  .pane-title{ margin:0 0 12px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.95rem; font-weight:700; color:var(--fc-ink); }
  .chart-wrap{ position:relative; height:300px; }

  .info-note{ background:var(--fc-primary-light); border-radius:9px; padding:11px 14px; margin-bottom:18px; font-size:.78rem; color:var(--fc-ink-soft); line-height:1.5; }
  .info-note strong{ color:var(--fc-primary-dark); }

  .loading{ text-align:center; padding:28px; color:var(--fc-muted); font-size:.8rem; }
  .error-msg{ background:var(--fc-red-bg); color:var(--fc-red); padding:12px; border-radius:9px; text-align:center; font-size:.8rem; }
</style>
@endsection

@section('content')

<div class="fc-head">
  <h1>Sales Forecast</h1>
  <p>Gross sales summary per zone for PISTAVORA Lipa. Click a zone to open its full forecast dashboard.</p>
  <span class="fc-live">Live · <span id="generatedAt">Loading…</span></span>
</div>

<div class="info-note">
  <strong>How this works:</strong> Gross is the total sales of the zone from the 1st of this month until today (completed transactions only).
  The % change compares it against the <em>same number of days</em> of last month, so the comparison is fair mid-month.
</div>

{{-- Zone cards (rendered by JS; skeleton placeholders keep the layout stable) --}}
<div class="zone-grid" id="zoneGrid">
  @foreach ($zones as $z)
    <a class="zone-card" href="{{ $zoneUrls[$z] ?? '#' }}">
      <div class="zone-top">
        <span class="zone-name"><i class="fas {{ $zoneIcons[$z] ?? 'fa-chart-line' }}"></i> {{ $z }}</span>
        <span class="zone-go">Open dashboard <i class="fas fa-arrow-right"></i></span>
      </div>
      <div class="loading">Loading…</div>
    </a>
  @endforeach
</div>

<div class="content-panel">
  <h3 class="pane-title">Gross Trend per Zone (Last 6 Months)</h3>
  <div class="chart-wrap"><canvas id="chartZones"></canvas></div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ML_API    = '{{ url("/api/ml") }}';
  const ZONE_URLS = @json($zoneUrls);   // change this if your zone dashboard URL is different
  const ICONS     = @json($zoneIcons);
  const COLORS    = { 'Skataya': '#4F46E5', 'Piyestarides': '#FF7A59', 'Dinolakbay': '#14B8A6' };

  Chart.defaults.font.family = 'Inter, sans-serif';
  Chart.defaults.color = '#33344A';

  function peso(n) {
      return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function trendBadge(z) {
      const pct = Math.abs(z.change_pct) + '%';
      if (z.trend === 'up')   return `<span class="trend-up">▲ ${pct}</span>`;
      if (z.trend === 'down') return `<span class="trend-down">▼ ${pct}</span>`;
      return `<span class="trend-stable">→ Stable</span>`;
  }

  function renderCards(data) {
      const grid = document.getElementById('zoneGrid');

      grid.innerHTML = data.zones.map(z => `
        <a class="zone-card" href="${ZONE_URLS[z.zone] || '#'}" aria-label="Open ${z.zone} dashboard">
          <div class="zone-top">
            <span class="zone-name"><i class="fas ${ICONS[z.zone] || 'fa-chart-line'}"></i> ${z.zone}</span>
            <span class="zone-go">Open dashboard <i class="fas fa-arrow-right"></i></span>
          </div>
          <div class="zone-label">Gross This Month</div>
          <div class="zone-gross">${peso(z.this_gross)}</div>
          <div class="zone-meta">
            ${trendBadge(z)}
            <span>vs ${peso(z.last_gross)} same period last month</span>
          </div>
          <div class="zone-meta" style="margin-top:6px">${Number(z.transactions).toLocaleString()} transactions this month</div>
          <div class="spark-wrap"><canvas id="spark-${z.zone.replace(/\s+/g, '-')}"></canvas></div>
        </a>`).join('');

      // Sparklines
      data.zones.forEach(z => {
          const ctx = document.getElementById('spark-' + z.zone.replace(/\s+/g, '-'));
          if (!ctx) return;
          const color = COLORS[z.zone] || '#4F46E5';
          new Chart(ctx, {
              type: 'line',
              data: {
                  labels: data.months,
                  datasets: [{ data: z.series, borderColor: color, backgroundColor: color + '22', fill: true, tension: 0.4, pointRadius: 0, borderWidth: 2 }]
              },
              options: {
                  responsive: true, maintainAspectRatio: false,
                  plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => peso(c.parsed.y) } } },
                  scales: { x: { display: false }, y: { display: false, beginAtZero: true } }
              }
          });
      });
  }

  function renderComparison(data) {
      const ctx = document.getElementById('chartZones');
      const existing = Chart.getChart(ctx);
      if (existing) existing.destroy();

      new Chart(ctx, {
          type: 'line',
          data: {
              labels: data.months,
              datasets: data.zones.map(z => ({
                  label: z.zone,
                  data: z.series,
                  borderColor: COLORS[z.zone] || '#4F46E5',
                  backgroundColor: (COLORS[z.zone] || '#4F46E5') + '22',
                  tension: 0.4, pointRadius: 4, pointHoverRadius: 6, borderWidth: 2
              }))
          },
          options: {
              responsive: true, maintainAspectRatio: false,
              plugins: {
                  legend: { labels: { usePointStyle: true, boxWidth: 8 } },
                  tooltip: { callbacks: { label: c => c.dataset.label + ': ' + peso(c.parsed.y) } }
              },
              scales: {
                  y: { beginAtZero: true, grid: { color: '#ECEDF6' }, ticks: { callback: v => '₱' + Number(v).toLocaleString() } },
                  x: { grid: { display: false } }
              }
          }
      });
  }

  async function boot() {
      try {
          const r = await fetch(ML_API + '/zone-summary', { headers: { 'Accept': 'application/json' } });
          if (!r.ok) throw new Error('HTTP ' + r.status);
          const data = await r.json();

          document.getElementById('generatedAt').textContent = data.generated_at || '';
          renderCards(data);
          renderComparison(data);
      } catch (e) {
          console.error(e);
          document.getElementById('zoneGrid').innerHTML =
              `<div class="error-msg" style="grid-column:1/-1">Could not load zone summary.<br><small>${e}</small></div>`;
      }
  }

  boot();
</script>
@endpush