{{--
    resources/views/user/booking.blade.php  —  step-by-step booking wizard
    Route: GET  /app/booking                 -> user.booking          (BookingController@create)
           POST /app/booking/submit          -> user.booking.submit   (BookingController@submit)
           GET  /app/booking/{id}/status     -> user.booking.status   (BookingController@status, polled by step 5)

    Steps: 1 Who's coming (solo / couple / group)  2 Pick attractions (Dino Adventure / Field of Rides / RollerFever tabs)
           3 Date & time   4 Pay by QR Ph + upload proof   5 Voucher (blurred until admin approves)

    Expects: $catalog (BookingWizard::catalog), $deals (BookingWizard::deals), $resume (wizard summary array or null).
    The previous page is kept as booking-classic.blade.php.
--}}
@extends('layouts.user')

@section('title', 'Book a Visit')
@section('page-title', 'Plan your adventure')
@section('page-subtitle', 'Book your WonderPark day in 5 easy steps')
@section('body-class', 'page-uniform')

@push('styles')
<style>
.bw{
  --bw-surface:#fff; --bw-soft:#FBF8FB; --bw-text:var(--ink); --bw-sub:var(--ink-soft);
  --bw-line:var(--line); --bw-accent:var(--pink-deep);
  max-width:1080px; margin:0 auto; color:var(--bw-text); position:relative; padding-bottom:96px;
}
[data-theme="dark"] .bw{
  --bw-surface:var(--d-surface); --bw-soft:var(--d-surface-soft); --bw-text:var(--d-text);
  --bw-sub:var(--d-text-soft); --bw-line:var(--d-border);
}
.bw *{ box-sizing:border-box; }
.bw button{ font-family:inherit; }

/* ---------- progress track ---------- */
.bw-track{ list-style:none; margin:0 0 22px; padding:0; display:flex; position:relative; }
.bw-track::before{ content:""; position:absolute; left:10%; right:10%; top:22px; height:6px; border-radius:6px; background:var(--bw-line); }
.bw-track-fill{ position:absolute; left:10%; top:22px; height:6px; border-radius:6px; width:0; max-width:80%;
  background:linear-gradient(90deg,#FFC948,#F2762E,var(--pink-deep)); transition:width .6s var(--ease); }
.bw-node{ flex:1; text-align:center; position:relative; z-index:1; }
.bw-node i{ width:50px; height:50px; margin:0 auto; border-radius:50%; display:grid; place-items:center; font-style:normal; font-size:22px;
  background:var(--bw-surface); border:3px solid var(--bw-line); filter:grayscale(1); opacity:.7; transition:all .35s var(--ease); }
.bw-node span{ display:block; margin-top:7px; font-size:11.5px; font-weight:700; color:var(--bw-sub); }
.bw-node.done i{ filter:none; opacity:1; border-color:#FFC948; background:var(--gold-pale); }
.bw-node.now i{ filter:none; opacity:1; border-color:var(--pink-deep); transform:scale(1.14); animation:bwBob 1.6s ease-in-out infinite;
  box-shadow:0 8px 18px -6px rgba(184,40,80,.55); }
.bw-node.now span{ color:var(--bw-accent); }
@keyframes bwBob{ 0%,100%{ transform:scale(1.14) translateY(0);} 50%{ transform:scale(1.14) translateY(-5px);} }

/* ---------- stage / steps ---------- */
.bw-stage{ position:relative; }
.bw-step{ display:none; }
.bw-step.is-active{ display:block; animation:bwInR .45s var(--ease); }
.bw-step.from-left.is-active{ animation-name:bwInL; }
@keyframes bwInR{ from{ opacity:0; transform:translateX(36px);} to{ opacity:1; transform:none;} }
@keyframes bwInL{ from{ opacity:0; transform:translateX(-36px);} to{ opacity:1; transform:none;} }
.bw-h{ margin:0 0 4px; font-size:24px; font-weight:800; letter-spacing:-.01em; }
.bw-sub{ margin:0 0 20px; color:var(--bw-sub); font-size:14px; }
.bw-wave{ display:inline-block; transform-origin:70% 70%; animation:bwWave 2.2s ease-in-out infinite; }
@keyframes bwWave{ 0%,60%,100%{ transform:rotate(0);} 10%,30%{ transform:rotate(16deg);} 20%,40%{ transform:rotate(-10deg);} 50%{ transform:rotate(8deg);} }

/* ---------- step 1 ---------- */
.bw-party{ display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
.bw-pcard{ border:2px solid var(--bw-line); background:var(--bw-surface); border-radius:22px; padding:26px 12px 20px; cursor:pointer; text-align:center;
  color:var(--bw-text); transition:transform .25s var(--ease), box-shadow .25s var(--ease), border-color .2s; position:relative; overflow:hidden; }
.bw-pcard:hover{ transform:translateY(-6px) rotate(-1deg); box-shadow:var(--shadow-pop); }
.bw-pcard .bw-big{ font-size:54px; display:block; line-height:1; transition:transform .3s var(--ease); }
.bw-pcard:hover .bw-big{ transform:scale(1.18) rotate(-6deg); }
.bw-pcard b{ display:block; margin-top:12px; font-size:17px; }
.bw-pcard small{ display:block; margin-top:3px; color:var(--bw-sub); font-size:12.5px; }
.bw-pcard.sel{ border-color:var(--pink-deep); background:linear-gradient(160deg,var(--pink-pale),var(--gold-pale)); box-shadow:0 14px 30px -14px rgba(184,40,80,.5); }
[data-theme="dark"] .bw-pcard.sel{ background:linear-gradient(160deg,rgba(184,40,80,.22),rgba(255,201,72,.12)); }
.bw-pcard.sel::after{ content:"✓"; position:absolute; top:10px; right:12px; width:26px; height:26px; border-radius:50%; background:var(--pink-deep);
  color:#fff; font-size:14px; display:grid; place-items:center; animation:bwPop .35s var(--ease); }
@keyframes bwPop{ from{ transform:scale(0);} to{ transform:scale(1);} }
.bw-groupbox{ margin-top:18px; background:var(--bw-surface); border:2px dashed var(--pink-light); border-radius:20px; padding:20px; text-align:center; animation:bwInR .4s var(--ease); }
.bw-groupbox[hidden]{ display:none; }
.bw-stepper{ display:inline-flex; align-items:center; gap:14px; }
.bw-stepper button{ width:42px; height:42px; border-radius:50%; border:none; cursor:pointer; font-size:22px; font-weight:800; color:#fff;
  background:var(--pink-deep); transition:transform .15s; }
.bw-stepper button:active{ transform:scale(.88); }
.bw-stepper button:disabled{ opacity:.35; cursor:not-allowed; }
.bw-stepper output{ min-width:70px; font-size:40px; font-weight:800; display:inline-block; }
.bw-chips{ display:flex; gap:8px; justify-content:center; flex-wrap:wrap; margin-top:12px; }
.bw-chip{ border:1px solid var(--bw-line); background:var(--bw-soft); color:var(--bw-text); border-radius:999px; padding:6px 14px; font-size:13px; font-weight:700; cursor:pointer; }
.bw-chip:hover,.bw-chip.on{ border-color:var(--pink-deep); color:var(--pink-deep); background:var(--pink-pale); }
[data-theme="dark"] .bw-chip:hover,[data-theme="dark"] .bw-chip.on{ background:rgba(184,40,80,.2); }
.bw-hint{ margin-top:10px; font-size:13px; color:var(--bw-sub); }

/* ---------- step 2 ---------- */
.bw-tabs{ display:flex; gap:8px; overflow-x:auto; padding:2px 2px 10px; scrollbar-width:none; }
.bw-tabs::-webkit-scrollbar{ display:none; }
.bw-tab{ flex:1 0 auto; display:flex; align-items:center; justify-content:center; gap:8px; padding:12px 18px; border-radius:16px; cursor:pointer;
  border:2px solid var(--bw-line); background:var(--bw-surface); color:var(--bw-text); font-weight:800; font-size:14px; position:relative; transition:all .2s var(--ease); white-space:nowrap; }
.bw-tab em{ font-style:normal; font-size:20px; }
.bw-tab:hover{ transform:translateY(-2px); }
.bw-tab.on{ color:#fff; border-color:transparent; background:var(--tab-grad,var(--pink-deep)); box-shadow:0 10px 22px -10px rgba(0,0,0,.45); }
.bw-tab .bw-count{ min-width:22px; height:22px; border-radius:11px; background:var(--gold); color:#4a2f00; font-size:12px; display:none; place-items:center; padding:0 6px; }
.bw-tab .bw-count.show{ display:grid; animation:bwPop .3s var(--ease); }
.bw-banner{ margin:6px 0 16px; padding:12px 16px; border-radius:14px; font-size:13.5px; font-weight:600;
  background:linear-gradient(90deg,var(--gold-pale),var(--pink-pale)); color:#6b3b00; border:1px solid var(--gold); }
[data-theme="dark"] .bw-banner{ background:rgba(255,201,72,.12); color:#FFD98A; border-color:rgba(255,201,72,.35); }
.bw-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr)); gap:14px; }
.bw-card{ position:relative; border:2px solid var(--bw-line); background:var(--bw-surface); border-radius:20px; overflow:hidden; cursor:pointer; text-align:left;
  padding:0; color:var(--bw-text); display:flex; flex-direction:column; transition:transform .25s var(--ease), box-shadow .25s var(--ease), border-color .2s; }
.bw-card:hover{ transform:translateY(-5px); box-shadow:var(--shadow-pop); }
.bw-media{ height:118px; background:var(--tab-grad,var(--pink-deep)) center/cover no-repeat; display:grid; place-items:center; font-size:48px; position:relative; }
.bw-media span{ filter:drop-shadow(0 4px 6px rgba(0,0,0,.3)); transition:transform .3s var(--ease); }
.bw-card:hover .bw-media span{ transform:scale(1.2) rotate(-8deg); }
.bw-body{ padding:12px 14px 14px; display:flex; flex-direction:column; gap:4px; flex:1; }
.bw-body b{ font-size:14.5px; line-height:1.25; }
.bw-body p{ margin:0; font-size:12px; color:var(--bw-sub); line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.bw-price{ margin-top:auto; padding-top:6px; font-size:19px; font-weight:800; color:var(--pink-deep); }
.bw-price small{ font-size:11.5px; font-weight:600; color:var(--bw-sub); }
.bw-ribbon{ position:absolute; top:10px; left:-1px; padding:4px 12px 4px 10px; font-size:11px; font-weight:800; color:#fff; border-radius:0 999px 999px 0;
  background:linear-gradient(90deg,#F2762E,#E24B4A); box-shadow:0 4px 10px -4px rgba(0,0,0,.4); z-index:2; }
.bw-ribbon.gold{ background:linear-gradient(90deg,#FFC948,#F2A03E); color:#4a2f00; }
.bw-tick{ position:absolute; top:10px; right:10px; width:30px; height:30px; border-radius:50%; background:rgba(255,255,255,.85); border:2px solid #fff;
  display:grid; place-items:center; color:transparent; font-weight:800; z-index:2; transition:all .25s var(--ease); }
.bw-card.sel{ border-color:var(--pink-deep); box-shadow:0 0 0 3px var(--pink-light), 0 16px 30px -14px rgba(184,40,80,.55); }
.bw-card.sel .bw-tick{ background:var(--pink-deep); color:#fff; animation:bwPop .35s var(--ease); }
.bw-qty{ display:none; align-items:center; justify-content:space-between; gap:8px; padding:8px 14px; border-top:1px dashed var(--bw-line); background:var(--bw-soft); font-size:12px; font-weight:700; }
.bw-card.sel .bw-qty{ display:flex; }
.bw-mini{ display:inline-flex; align-items:center; gap:8px; }
.bw-mini button{ width:26px; height:26px; border-radius:50%; border:1px solid var(--bw-line); background:var(--bw-surface); color:var(--bw-text); font-weight:800; cursor:pointer; line-height:1; }
.bw-mini button:hover{ border-color:var(--pink-deep); color:var(--pink-deep); }
.bw-mini span{ min-width:18px; text-align:center; font-weight:800; }
.bw-extras{ margin-top:20px; padding:16px; border-radius:18px; background:var(--bw-surface); border:1px solid var(--bw-line); }
.bw-extras h4{ margin:0 0 10px; font-size:14px; }
.bw-extra{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding:8px 0; border-top:1px solid var(--bw-line); font-size:13.5px; }
.bw-extra:first-of-type{ border-top:none; }
.bw-extra small{ color:var(--bw-sub); }

/* ---------- step 3 ---------- */
.bw-when{ display:grid; grid-template-columns:1.05fr 1fr; gap:18px; }
.bw-panel{ background:var(--bw-surface); border:1px solid var(--bw-line); border-radius:20px; padding:18px; }
.bw-cal-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
.bw-cal-head b{ font-size:16px; }
.bw-cal-head button{ width:34px; height:34px; border-radius:50%; border:1px solid var(--bw-line); background:var(--bw-soft); color:var(--bw-text); cursor:pointer; font-size:16px; }
.bw-cal-head button:disabled{ opacity:.3; cursor:not-allowed; }
.bw-dow,.bw-days{ display:grid; grid-template-columns:repeat(7,1fr); gap:6px; text-align:center; }
.bw-dow span{ font-size:11px; font-weight:700; color:var(--bw-sub); padding-bottom:4px; }
.bw-day{ aspect-ratio:1; border-radius:50%; border:none; background:transparent; color:var(--bw-text); font-weight:700; font-size:14px; cursor:pointer; transition:all .18s var(--ease); }
.bw-day:hover:not(:disabled){ background:var(--pink-pale); color:var(--pink-deep); transform:scale(1.12); }
.bw-day:disabled{ opacity:.25; cursor:not-allowed; }
.bw-day.today{ box-shadow:inset 0 0 0 2px var(--gold); }
.bw-day.wk{ color:var(--pink-deep); }
.bw-day.sel{ background:var(--pink-deep)!important; color:#fff!important; transform:scale(1.12); box-shadow:0 8px 16px -6px rgba(184,40,80,.6); }
.bw-slots-h{ font-size:12px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color:var(--bw-sub); margin:14px 0 8px; }
.bw-slots-h:first-child{ margin-top:0; }
.bw-slots{ display:grid; grid-template-columns:repeat(auto-fill,minmax(92px,1fr)); gap:8px; }
.bw-slot{ padding:10px 4px; border-radius:12px; border:2px solid var(--bw-line); background:var(--bw-soft); color:var(--bw-text); font-weight:700; font-size:13px; cursor:pointer; transition:all .18s var(--ease); }
.bw-slot:hover{ border-color:var(--pink-deep); transform:translateY(-2px); }
.bw-slot.sel{ background:var(--pink-deep); border-color:var(--pink-deep); color:#fff; }
.bw-empty{ text-align:center; color:var(--bw-sub); padding:34px 10px; font-size:14px; }
.bw-empty b{ display:block; font-size:38px; margin-bottom:6px; }
.bw-sec{ grid-column:1/-1; font-size:13px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; color:var(--bw-sub); margin:10px 0 -2px; }
.bw-sec small{ font-weight:500; text-transform:none; letter-spacing:0; margin-left:6px; }
.bw-inc{ padding:0 14px 12px; }
.bw-inc-btn{ background:none; border:0; padding:0; font:inherit; font-size:12.5px; font-weight:700; color:var(--pink-deep); cursor:pointer; display:flex; gap:6px; align-items:center; }
.bw-inc ul{ margin:8px 0 0; padding-left:18px; font-size:12.5px; color:var(--bw-sub); line-height:1.5; text-align:left; cursor:default; }
.bw-inc ul[hidden]{ display:none; }

/* ---------- step 4 ---------- */
.bw-pay{ display:grid; grid-template-columns:1fr 1fr; gap:18px; align-items:start; }
.bw-receipt{ position:relative; background:var(--bw-surface); border:1px solid var(--bw-line); border-radius:20px; padding:20px 20px 16px; }
.bw-receipt::after{ content:""; position:absolute; left:0; right:0; bottom:-8px; height:8px;
  background:radial-gradient(circle at 8px 0,var(--bw-surface) 7px,transparent 8px) 0 0/16px 8px repeat-x; filter:drop-shadow(0 1px 0 var(--bw-line)); }
.bw-receipt h4{ margin:0 0 10px; font-size:15px; }
.bw-row{ display:flex; justify-content:space-between; gap:12px; padding:6px 0; font-size:13.5px; }
.bw-row small{ display:block; color:var(--bw-sub); font-size:11.5px; }
.bw-row.disc{ color:#0C8B80; font-weight:700; }
.bw-row.sum{ border-top:2px dashed var(--bw-line); margin-top:8px; padding-top:12px; font-size:18px; font-weight:800; }
.bw-row.sum span:last-child{ color:var(--pink-deep); }
.bw-qr{ text-align:center; }
.bw-qr-frame{ background:#fff; border-radius:18px; padding:14px; display:inline-block; border:3px solid var(--pink-light); position:relative; }
.bw-qr-frame img{ width:100%; max-width:250px; aspect-ratio:1; object-fit:contain; display:block; }
.bw-qr-frame::before{ content:""; position:absolute; left:14px; right:14px; height:3px; border-radius:3px; top:14px;
  background:linear-gradient(90deg,transparent,var(--pink-deep),transparent); animation:bwScan 2.4s ease-in-out infinite; }
@keyframes bwScan{ 0%,100%{ top:14px; opacity:.2;} 50%{ top:calc(100% - 16px); opacity:1;} }
.bw-qr p{ font-size:13px; color:var(--bw-sub); margin:10px 0 4px; }
.bw-amount{ font-size:26px; font-weight:800; color:var(--pink-deep); }
.bw-drop{ margin-top:14px; border:2px dashed var(--pink-light); border-radius:18px; padding:18px; text-align:center; cursor:pointer; background:var(--bw-soft); transition:all .2s; }
.bw-drop:hover,.bw-drop.over{ border-color:var(--pink-deep); background:var(--pink-pale); }
[data-theme="dark"] .bw-drop:hover,[data-theme="dark"] .bw-drop.over{ background:rgba(184,40,80,.16); }
.bw-drop b{ display:block; font-size:14px; }
.bw-drop small{ color:var(--bw-sub); font-size:12px; }
.bw-drop img{ max-height:120px; border-radius:10px; margin-top:10px; }
.bw-drop .bw-fname{ display:block; margin-top:8px; font-weight:700; font-size:13px; color:#0C8B80; }
.bw-note{ margin-top:12px; font-size:12.5px; color:var(--bw-sub); background:var(--bw-soft); border-radius:12px; padding:10px 12px; }
.bw-err{ margin-top:12px; padding:10px 14px; border-radius:12px; background:var(--rose-pale); color:var(--rose-deep); font-weight:600; font-size:13px; }
[data-theme="dark"] .bw-err{ background:rgba(226,75,74,.16); }

/* ---------- step 5 ---------- */
.bw-done{ max-width:560px; margin:0 auto; text-align:center; }
.bw-ticket{ position:relative; text-align:left; background:var(--bw-surface); border-radius:24px; border:1px solid var(--bw-line); box-shadow:var(--shadow-pop); overflow:hidden; }
.bw-ticket-top{ padding:22px 22px 18px; background:linear-gradient(135deg,var(--pink-deep),#F2762E); color:#fff; }
.bw-ticket-top small{ opacity:.85; font-weight:700; letter-spacing:.08em; font-size:11px; }
.bw-ticket-top h3{ margin:4px 0 0; font-size:22px; }
.bw-ticket-mid{ padding:16px 22px; font-size:13.5px; }
.bw-perf{ position:relative; border-top:3px dotted var(--bw-line); margin:0 18px; }
.bw-perf::before,.bw-perf::after{ content:""; position:absolute; top:-15px; width:26px; height:26px; border-radius:50%; background:var(--bg); }
.bw-perf::before{ left:-31px; } .bw-perf::after{ right:-31px; }
[data-theme="dark"] .bw-perf::before,[data-theme="dark"] .bw-perf::after{ background:var(--d-bg); }
.bw-ticket-bot{ padding:18px 22px 24px; text-align:center; }
.bw-vlabel{ font-size:11px; font-weight:800; letter-spacing:.12em; color:var(--bw-sub); }
.bw-vwrap{ position:relative; margin:10px auto 4px; display:inline-block; }
.bw-vcode{ font-family:'Courier New',monospace; font-size:34px; font-weight:800; letter-spacing:.2em; padding:10px 18px; border-radius:14px;
  background:var(--gold-pale); color:#6b3b00; border:2px dashed var(--gold); filter:blur(9px); transition:filter 1.1s var(--ease); user-select:none; }
[data-theme="dark"] .bw-vcode{ background:rgba(255,201,72,.12); color:#FFD98A; }
.bw-vcode.reveal{ filter:blur(0); user-select:all; animation:bwReveal 1.1s var(--ease); }
@keyframes bwReveal{ 0%{ transform:scale(.8) rotate(-3deg);} 55%{ transform:scale(1.12) rotate(2deg);} 100%{ transform:none;} }
.bw-lock{ position:absolute; inset:0; display:grid; place-items:center; font-size:30px; pointer-events:none; transition:opacity .5s; }
.bw-lock.gone{ opacity:0; }
.bw-pill{ display:inline-flex; align-items:center; gap:8px; padding:7px 16px; border-radius:999px; font-weight:800; font-size:13px; margin:10px 0 4px; background:var(--amber-pale); color:var(--amber-deep); }
.bw-pill::before{ content:""; width:9px; height:9px; border-radius:50%; background:currentColor; animation:bwPulse 1.4s ease-in-out infinite; }
.bw-pill.ok{ background:var(--teal-pale); color:var(--teal-deep); }
.bw-pill.ok::before{ animation:none; }
.bw-pill.bad{ background:var(--rose-pale); color:var(--rose-deep); }
@keyframes bwPulse{ 0%,100%{ transform:scale(1); opacity:1;} 50%{ transform:scale(1.7); opacity:.35;} }
.bw-line{ display:flex; align-items:flex-start; justify-content:center; margin:18px 0 6px; }
.bw-line div{ flex:1; text-align:center; font-size:11.5px; font-weight:700; color:var(--bw-sub); position:relative; }
.bw-line div::before{ content:""; display:block; width:16px; height:16px; margin:0 auto 6px; border-radius:50%; background:var(--bw-line); position:relative; z-index:1; }
.bw-line div::after{ content:""; position:absolute; top:7px; left:-50%; width:100%; height:3px; background:var(--bw-line); }
.bw-line div:first-child::after{ display:none; }
.bw-line div.on{ color:var(--bw-text); }
.bw-line div.on::before,.bw-line div.on::after{ background:var(--teal); }
.bw-line div.cur::before{ background:var(--gold); animation:bwPulse 1.4s ease-in-out infinite; }
.bw-actions{ display:flex; gap:10px; flex-wrap:wrap; justify-content:center; margin-top:18px; }
.bw-confetti{ position:fixed; inset:0; pointer-events:none; z-index:60; }

/* ---------- bottom action bar ---------- */
.bw-bar{ position:sticky; bottom:84px; z-index:15; margin-top:22px; display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:20px;
  background:var(--bw-surface); border:1px solid var(--bw-line); box-shadow:var(--shadow-pop); }
.bw-bar[hidden]{ display:none; }
.bw-bar-info{ flex:1; min-width:0; font-size:12.5px; color:var(--bw-sub); line-height:1.35; }
.bw-bar-info b{ display:block; font-size:19px; color:var(--bw-text); }
.bw-btn{ border:none; cursor:pointer; border-radius:14px; padding:13px 22px; font-weight:800; font-size:14px; transition:all .18s var(--ease); white-space:nowrap; text-decoration:none; display:inline-block; }
.bw-btn.go{ background:linear-gradient(135deg,var(--pink-deep),#F2762E); color:#fff; box-shadow:0 10px 20px -10px rgba(184,40,80,.7); }
.bw-btn.go:hover:not(:disabled){ transform:translateY(-2px); }
.bw-btn.go:disabled{ opacity:.4; cursor:not-allowed; box-shadow:none; }
.bw-btn.back{ background:var(--bw-soft); color:var(--bw-text); border:1px solid var(--bw-line); }
.bw-btn.ghost{ background:transparent; color:var(--pink-deep); border:2px solid var(--pink-light); }
.bw-btn.ghost:hover{ background:var(--pink-pale); }
.bw-spin{ display:inline-block; width:14px; height:14px; border:2px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation:bwSpin .7s linear infinite; vertical-align:-2px; margin-right:8px; }
@keyframes bwSpin{ to{ transform:rotate(360deg);} }
.bw-shake{ animation:bwShake .4s; }
@keyframes bwShake{ 0%,100%{ transform:none;} 25%{ transform:translateX(-7px);} 75%{ transform:translateX(7px);} }

@media (min-width:960px){ .bw-bar{ bottom:16px; } }
@media (max-width:720px){
  .bw-party{ gap:8px; } .bw-pcard{ padding:18px 6px 14px; border-radius:18px; } .bw-pcard .bw-big{ font-size:38px; } .bw-pcard b{ font-size:14px; } .bw-pcard small{ font-size:11px; }
  .bw-when,.bw-pay{ grid-template-columns:1fr; }
  .bw-grid{ grid-template-columns:repeat(2,1fr); gap:10px; } .bw-media{ height:92px; font-size:38px; }
  .bw-h{ font-size:20px; } .bw-node span{ font-size:10px; } .bw-node i{ width:42px; height:42px; font-size:18px; } .bw-track::before,.bw-track-fill{ top:19px; }
  .bw-vcode{ font-size:24px; }
}
@media (prefers-reduced-motion:reduce){ .bw *{ animation:none!important; transition:none!important; } }
.bw-btn.go.dim{ opacity:.5; box-shadow:none; }
</style>
@endpush

@section('content')
<div class="bw" id="bw">

    <ol class="bw-track" aria-label="Booking progress">
        <div class="bw-track-fill"></div>
        <li class="bw-node now"><i>🎟️</i><span>Who's coming</span></li>
        <li class="bw-node"><i>&#127914;</i><span>Pick attractions</span></li>
        <li class="bw-node"><i>📅</i><span>Date &amp; time</span></li>
        <li class="bw-node"><i>📱</i><span>Pay</span></li>
        <li class="bw-node"><i>🎁</i><span>Voucher</span></li>
    </ol>

    <div class="bw-stage">

        {{-- STEP 1 --}}
        <section class="bw-step is-active" data-step="1">
            <h3 class="bw-h">Who's coming to play? <span class="bw-wave">👋</span></h3>
            <p class="bw-sub">How many are coming? Pick one to get started.</p>
            <div class="bw-party">
                <button type="button" class="bw-pcard" data-party="solo"><span class="bw-big">&#128587;</span><b>Solo</b><small>1 person</small></button>
                <button type="button" class="bw-pcard" data-party="couple"><span class="bw-big">&#129489;&#8205;&#129309;&#8205;&#129489;</span><b>Duo</b><small>2 people</small></button>
                <button type="button" class="bw-pcard" data-party="group"><span class="bw-big">&#127881;</span><b>Group</b><small>3 or more people</small></button>
            </div>
            <div class="bw-groupbox" id="bwGroupBox" hidden>
                <b>How many are you?</b>
                <div style="margin-top:10px" class="bw-stepper">
                    <button type="button" id="bwGroupMinus" aria-label="Fewer guests">−</button>
                    <output id="bwGroupN">5</output>
                    <button type="button" id="bwGroupPlus" aria-label="More guests">+</button>
                </div>
                <div class="bw-chips">
                    <button type="button" class="bw-chip" data-n="5">5</button>
                    <button type="button" class="bw-chip" data-n="8">8</button>
                    <button type="button" class="bw-chip" data-n="10">10</button>
                    <button type="button" class="bw-chip" data-n="15">15</button>
                    <button type="button" class="bw-chip" data-n="20">20</button>
                </div>
                <p class="bw-hint">Groups of 5+ unlock special deals 🎁</p>
            </div>
        </section>

        {{-- STEP 2 --}}
        <section class="bw-step" data-step="2">
            <h3 class="bw-h">Choose your attractions <span class="bw-wave">&#127914;</span></h3>
            <p class="bw-sub">Tap to pick as many as you like — prices are per person.</p>
            <div class="bw-tabs" id="bwTabs"></div>
            <div class="bw-banner" id="bwBanner" hidden></div>
            <div class="bw-grid" id="bwGrid"></div>
            <div class="bw-extras" id="bwExtras" hidden></div>
        </section>

        {{-- STEP 3 --}}
        <section class="bw-step" data-step="3">
            <h3 class="bw-h">When are you visiting? <span class="bw-wave">📅</span></h3>
            <p class="bw-sub">Open Mon–Fri 9 AM–9 PM · Sat–Sun 10 AM–9 PM.</p>
            <div class="bw-when">
                <div class="bw-panel">
                    <div class="bw-cal-head">
                        <button type="button" id="bwPrev" aria-label="Previous month">‹</button>
                        <b id="bwMonth"></b>
                        <button type="button" id="bwNextM" aria-label="Next month">›</button>
                    </div>
                    <div class="bw-dow"><span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span></div>
                    <div class="bw-days" id="bwDays"></div>
                </div>
                <div class="bw-panel" id="bwSlots"></div>
            </div>
        </section>

        {{-- STEP 4 --}}
        <section class="bw-step" data-step="4">
            <h3 class="bw-h">Scan, pay &amp; send proof <span class="bw-wave">📱</span></h3>
            <p class="bw-sub">Pay online with QR Ph, then upload your screenshot. Our team will review it.</p>
            <div class="bw-pay">
                <div class="bw-receipt" id="bwReceipt"></div>
                <div class="bw-panel bw-qr">
                    <div class="bw-qr-frame"><img src="{{ asset('images/instapay-qr.png') }}" alt="QR Ph payment code"></div>
                    <p>Scan with GCash, Maya or any banking app</p>
                    <div class="bw-amount" id="bwAmount"></div>
                    <div class="bw-drop" id="bwDrop"><b>📸 Upload payment screenshot</b><small>Tap or drag a file here · JPG, PNG, WEBP or PDF · max 5MB</small></div>
                    <input type="file" id="bwFile" accept="image/*,application/pdf" hidden>
                    <div class="bw-err" id="bwErr" hidden></div>
                    <div class="bw-note">After you submit, your booking goes to <b>In review</b>. Your voucher unlocks once it's approved.</div>
                </div>
            </div>
        </section>

        {{-- STEP 5 --}}
        <section class="bw-step" data-step="5">
            <div class="bw-done" id="bwDone"></div>
        </section>
    </div>

    <div class="bw-bar" id="bwBar">
        <button type="button" class="bw-btn back" id="bwBack">← Back</button>
        <div class="bw-bar-info" id="bwBarInfo"></div>
        <button type="button" class="bw-btn go" id="bwNext">Next →</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.BW = {
    tabs: @json($catalog),
    deals: @json($deals),
    resume: @json($resume),
    imgBase: @json(asset('images') . '/'),
    csrf: @json(csrf_token()),
    urls: {
        submit:   @json(route('user.booking.submit')),
        status:   @json(route('user.booking.status', '__ID__')),
        receipt:  @json(route('user.bookings.receipt', '__ID__')),
        review:   @json(route('user.bookings.review', '__ID__')),
        waiver:   @json(route('user.waiver')),
        bookings: @json(route('user.bookings')),
        booking:  @json(route('user.booking')),
        login:    @json(url('/login'))
    }
};
</script>
<script>
(function () {
  'use strict';
  var BW = window.BW;
  var root = document.getElementById('bw');
  if (!root || !BW) return;

  var TABS = BW.tabs, D = BW.deals;
  var TAB_KEYS = Object.keys(TABS);
  var GRAD = {
    dino_adventure: 'linear-gradient(135deg,#12B5A6,#6FD08C)',
    field_of_rides: 'linear-gradient(135deg,#FFC948,#F2762E)',
    rollerfever:    'linear-gradient(135deg,#B82850,#7A4CE0)'
  };
  var ITEM = {}, EXTRA = {};
  TAB_KEYS.forEach(function (t) {
    TABS[t].items.forEach(function (i) { ITEM[i.key] = i; });
    TABS[t].extras.forEach(function (e) { EXTRA[e.key] = e; });
  });

  var state = {
    step: 1, party: null, guests: 0, tab: TAB_KEYS[0],
    items: {}, extras: {}, date: null, time: null,
    month: firstOfMonth(new Date()), file: null, booking: null, busy: false, poll: null
  };

  function $(s, r) { return (r || root).querySelector(s); }
  function $$(s, r) { return Array.prototype.slice.call((r || root).querySelectorAll(s)); }
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function money(n) { return '\u20B1' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 2 }); }
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function iso(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
  function firstOfMonth(d) { return new Date(d.getFullYear(), d.getMonth(), 1); }
  function today() { var t = new Date(); t.setHours(0, 0, 0, 0); return t; }
  function isWeekend(d) { return d.getDay() === 0 || d.getDay() === 6; }
  function dayBlocked(cell) {
    return Object.keys(state.items).some(function (k) {
      var it = ITEM[k]; if (!it || !it.flat || !it.days) return false;
      return it.days === 'weekday' ? isWeekend(cell) : !isWeekend(cell);
    });
  }
  function plural(n, w) { return n + ' ' + w + (n === 1 ? '' : 's'); }

  /* ---------------- pricing (mirror of BookingWizard::price) ---------------- */
  function dealApplies(it) {
    var list = (D.four_plus_one.packages || {})[it.service] || [];
    return list.indexOf(it.package) !== -1;
  }
  function tierFor(it, g) {
    var t = it.tiers || [];
    for (var i = 0; i < t.length; i++) { if (g <= t[i].pax) return t[i]; }
    return null;
  }
  function partyPrice(it) {
    var t = tierFor(it, state.guests), top = it.tiers[it.tiers.length - 1];
    if (t) return money(t.price) + ' <small>for up to ' + t.pax + ' guests</small>';
    return 'From ' + money(it.tiers[0].price) + ' <small>fits ' + it.tiers[0].pax + '\u2013' + top.pax + ' guests only</small>';
  }
  function partyTooBig(it) {
    var top = it.tiers[it.tiers.length - 1].pax;
    btnNext.classList.remove('bw-shake'); void btnNext.offsetWidth; btnNext.classList.add('bw-shake');
    barInfo.innerHTML = '<b style="color:var(--pink-deep)">Too many guests</b>This package fits up to ' + top + ' guests';
    clearTimeout(hintTimer); hintTimer = setTimeout(renderBar, 2600);
  }
  function incHTML(it) {
    var inc = it.inclusions || [];
    var tiersLi = it.flat ? '<li style="list-style:none;margin-left:-18px;margin-bottom:6px"><b>Price by number of guests</b><br>' +
      it.tiers.map(function (t) { return 'Up to ' + t.pax + ' guests \u2014 ' + money(t.price); }).join('<br>') + '</li>' : '';
    if (!inc.length && !tiersLi) return '';
    return '<div class="bw-inc"><button type="button" class="bw-inc-btn" data-inc aria-expanded="false">What\u2019s included <span>+</span></button><ul hidden>' + tiersLi +
      inc.map(function (x) { return '<li>' + esc(x) + '</li>'; }).join('') + '</ul></div>';
  }
  function gridHTML(items) {
    var pass = items.filter(function (i) { return !i.flat; }), party = items.filter(function (i) { return i.flat; });
    var h = '';
    if (party.length && pass.length) h += '<div class="bw-sec">\uD83C\uDFAB Per-person passes</div>';
    h += pass.map(cardHTML).join('');
    if (party.length) h += '<div class="bw-sec">\uD83C\uDF89 Party packages<small>Flat price based on your number of guests</small></div>' + party.map(cardHTML).join('');
    return h;
  }
  function price() {
    var subtotal = 0, bundle = 0, groupBase = 0, lines = [], extras = [], extrasTotal = 0;
    Object.keys(state.items).forEach(function (k) {
      var it = ITEM[k], qty = state.items[k];
      if (!it) return;
      if (it.flat) { var tp = tierFor(it, state.guests), up = tp ? tp.price : 0; subtotal += up; lines.push({ it: it, qty: 1, gross: up, free: 0, disc: 0, tier: tp }); return; }
      var gross = it.unit_price * qty, free = 0;
      if (dealApplies(it) && qty >= D.four_plus_one.min_qty) free = Math.floor(qty / D.four_plus_one.min_qty);
      var disc = free * it.unit_price;
      subtotal += gross; bundle += disc;
      if (!free) groupBase += gross;
      lines.push({ it: it, qty: qty, gross: gross, free: free, disc: disc });
    });
    var pct = 0;
    D.group_tiers.forEach(function (t) { if (state.guests >= t.min_guests) pct = Math.max(pct, t.pct); });
    var groupDisc = Math.round(groupBase * pct / 100);
    Object.keys(state.extras).forEach(function (k) {
      var e = EXTRA[k], q = state.extras[k];
      if (!e || !q) return;
      extrasTotal += e.unit_price * q;
      extras.push({ e: e, qty: q, total: e.unit_price * q });
    });
    return { lines: lines, extras: extras, subtotal: subtotal, bundle: bundle, pct: pct, groupDisc: groupDisc,
      discount: bundle + groupDisc, extrasTotal: extrasTotal, total: subtotal - bundle - groupDisc + extrasTotal };
  }
  function selectedCount() { return Object.keys(state.items).length; }

  /* ---------------- navigation ---------------- */
  var stepEls = $$('.bw-step');
  var bar = $('#bwBar'), btnBack = $('#bwBack'), btnNext = $('#bwNext'), barInfo = $('#bwBarInfo');

  function go(n, dir) {
    stopPoll();
    state.step = n;
    stepEls.forEach(function (el) {
      var on = +el.getAttribute('data-step') === n;
      el.classList.toggle('is-active', on);
      el.classList.toggle('from-left', dir === 'back');
    });
    if (n === 2) renderStep2();
    if (n === 3) renderStep3();
    if (n === 4) renderStep4();
    if (n === 5) renderStep5();
    renderTrack(); renderBar();
    root.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
  function renderTrack() {
    $$('.bw-node').forEach(function (el, i) {
      el.classList.toggle('done', i + 1 < state.step);
      el.classList.toggle('now', i + 1 === state.step);
    });
    $('.bw-track-fill').style.width = ((state.step - 1) / 4 * 80) + '%';
  }
  function canNext() {
    if (state.step === 1) return state.guests >= 1 && !!state.party;
    if (state.step === 2) return selectedCount() > 0;
    if (state.step === 3) return !!(state.date && state.time);
    if (state.step === 4) return !!state.file;
    return false;
  }
  var HINT = {
    1: 'Pick who is coming first \uD83D\uDC46', 2: 'Pick at least one attraction \uD83C\uDFAA',
    3: 'Choose a date and a time slot \uD83D\uDCC5', 4: 'Upload your payment screenshot \uD83D\uDCF8'
  };
  var hintTimer = null;
  function renderBar() {
    bar.hidden = state.step === 5;
    btnBack.style.visibility = state.step === 1 ? 'hidden' : 'visible';
    btnNext.innerHTML = state.step === 4 ? 'Submit for review \uD83C\uDF9F\uFE0F' : 'Next \u2192';
    btnNext.classList.toggle('dim', !canNext());
    btnNext.disabled = state.busy;
    var t = '';
    if (state.step === 1) {
      t = state.guests ? '<b>' + plural(state.guests, 'guest') + '</b>' + (state.party === 'group' ? 'Group adventure \uD83C\uDF89' : (state.party === 'couple' ? 'Duo day \uD83E\uDD1D' : 'Me time \uD83D\uDE0E')) : '<b>Who is coming?</b>Pick one above';
    } else if (state.step === 4) {
      t = '<b>' + money(price().total) + '</b>Total to pay via QR Ph';
    } else {
      var p = price();
      t = '<b>' + money(p.total) + '</b>' + plural(state.guests, 'guest') + ' \u00B7 ' + plural(selectedCount(), 'pick');
      if (state.step === 3 && state.date) {
        t += state.time ? ' \u00B7 ' + dateLabel(state.date) + ' ' + timeLabel(state.time) : ' \u00B7 ' + dateLabel(state.date);
      }
    }
    barInfo.innerHTML = t;
  }
  function flashHint() {
    btnNext.classList.remove('bw-shake'); void btnNext.offsetWidth; btnNext.classList.add('bw-shake');
    var old = barInfo.innerHTML;
    barInfo.innerHTML = '<b style="color:var(--pink-deep)">Almost there!</b>' + HINT[state.step];
    clearTimeout(hintTimer);
    hintTimer = setTimeout(renderBar, 2600);
    void old;
  }
  btnBack.addEventListener('click', function () { if (state.step > 1) go(state.step - 1, 'back'); });
  btnNext.addEventListener('click', function () {
    if (state.busy) return;
    if (!canNext()) return flashHint();
    if (state.step === 4) return submit();
    go(state.step + 1, 'fwd');
  });

  /* ---------------- step 1: party ---------------- */
  function setGuests(n) {
    var prev = state.guests;
    state.guests = n;
    Object.keys(state.items).forEach(function (k) {
      if (state.items[k] === prev || state.items[k] > n) state.items[k] = n;
    });
    var out = $('#bwGroupN'); if (out) out.textContent = n;
    $$('.bw-chip').forEach(function (c) { c.classList.toggle('on', +c.getAttribute('data-n') === n && state.party === 'group'); });
    $('#bwGroupMinus').disabled = n <= 3;
    $('#bwGroupPlus').disabled = n >= 100;
  }
  $$('.bw-pcard').forEach(function (c) {
    c.addEventListener('click', function () {
      var p = c.getAttribute('data-party');
      state.party = p;
      $$('.bw-pcard').forEach(function (x) { x.classList.toggle('sel', x === c); });
      $('#bwGroupBox').hidden = p !== 'group';
      setGuests(p === 'solo' ? 1 : p === 'couple' ? 2 : Math.max(state.guests > 2 ? state.guests : 5, 3));
      renderBar();
    });
  });
  $('#bwGroupMinus').addEventListener('click', function () { setGuests(Math.max(3, state.guests - 1)); renderBar(); });
  $('#bwGroupPlus').addEventListener('click', function () { setGuests(Math.min(100, state.guests + 1)); renderBar(); });
  $$('.bw-chip').forEach(function (c) {
    c.addEventListener('click', function () { setGuests(+c.getAttribute('data-n')); renderBar(); });
  });

  /* ---------------- step 2: rides ---------------- */
  function bannerText() {
    var g = state.guests, tiers = D.group_tiers.slice().sort(function (a, b) { return a.min_guests - b.min_guests; });
    var hit = null, next = null;
    tiers.forEach(function (t) { if (g >= t.min_guests) hit = t; else if (!next) next = t; });
    var msg = '';
    if (hit) msg = '\uD83C\uDF89 <b>Group deal unlocked!</b> ' + hit.pct + '% off your attractions.';
    else if (next) msg = '\uD83D\uDCA1 Bring ' + plural(next.min_guests - g, 'more friend') + ' and get <b>' + next.pct + '% off</b> (groups of ' + next.min_guests + '+).';
    var hasDeal = false; // banner removed; the card ribbons already show the deal
    if (hasDeal) {
      var m = D.four_plus_one.min_qty;
      msg += (msg ? '<br>' : '') + '\uD83C\uDF81 <b>Buy ' + (m - 1) + ', get 1 free</b> on these passes when ' + m + '+ guests skate together.';
    }
    return msg;
  }
  function cardHTML(it) {
    var sel = state.items[it.key] != null, qty = state.items[it.key] || state.guests;
    var bg = it.image ? 'background-image:linear-gradient(0deg,rgba(0,0,0,.28),rgba(0,0,0,0)),url(\'' + BW.imgBase + esc(it.image) + '\');' : '';
    var rib = false /* deal ribbon removed */ ? '<span class="bw-ribbon">\uD83C\uDF81 ' + esc(it.deal) + '</span>' : (it.badge ? '<span class="bw-ribbon gold">\u2B50 ' + esc(it.badge) + '</span>' : '');
    return '<div class="bw-card' + (sel ? ' sel' : '') + '" role="button" tabindex="0" data-key="' + esc(it.key) + '">' +
      rib + '<span class="bw-tick">\u2713</span>' +
      '<div class="bw-media" style="' + bg + '">' + (it.image ? '' : '<span>' + it.emoji + '</span>') + '</div>' +
      '<div class="bw-body"><b>' + esc(it.name) + '</b><p>' + esc(it.desc) + '</p>' +
      '<div class="bw-price">' + (it.flat ? partyPrice(it) : money(it.unit_price) + ' <small>/ person</small>') + '</div>' + incHTML(it) + '</div>' +
      (state.guests > 1 && !it.flat ? '<div class="bw-qty"><span>Guests riding</span><span class="bw-mini"><button type="button" data-d="-1">\u2212</button><span data-q>' + qty + '</span><button type="button" data-d="1">+</button></span></div>' : '') +
      '</div>';
  }
  function renderStep2() {
    Object.keys(state.items).forEach(function (k) { var o = ITEM[k]; if (o && o.flat && !tierFor(o, state.guests)) delete state.items[k]; });
    var tabs = $('#bwTabs');
    tabs.innerHTML = TAB_KEYS.map(function (k) {
      return '<button type="button" class="bw-tab' + (k === state.tab ? ' on' : '') + '" data-tab="' + k + '" style="--tab-grad:' + GRAD[k] + '">' +
        '<em>' + TABS[k].emoji + '</em>' + esc(TABS[k].label) + '<span class="bw-count" data-count="' + k + '"></span></button>';
    }).join('');
    $$('.bw-tab', tabs).forEach(function (b) {
      b.addEventListener('click', function () { state.tab = b.getAttribute('data-tab'); renderStep2(); });
    });
    var t = TABS[state.tab];
    var bn = bannerText(); var el = $('#bwBanner'); el.innerHTML = bn; el.hidden = !bn;
    var grid = $('#bwGrid');
    grid.style.setProperty('--tab-grad', GRAD[state.tab]);
    grid.innerHTML = t.items.length ? gridHTML(t.items) : '<div class="bw-empty"><b>\uD83D\uDEA7</b>Nothing to book here yet.</div>';
    renderExtras(); updateCounts();
  }
  function updateCounts() {
    TAB_KEYS.forEach(function (k) {
      var n = TABS[k].items.filter(function (i) { return state.items[i.key] != null; }).length;
      var el = $('[data-count="' + k + '"]');
      if (el) { el.textContent = n; el.classList.toggle('show', n > 0); }
    });
  }
  function renderExtras() {
    var box = $('#bwExtras'), ex = TABS[state.tab].extras;
    if (!ex.length) { box.hidden = true; box.innerHTML = ''; return; }
    box.hidden = false;
    box.innerHTML = '<h4>\u2728 Add-ons <small style="font-weight:500;color:var(--bw-sub)">(optional)</small></h4>' + ex.map(function (e) {
      var q = state.extras[e.key] || 0;
      return '<div class="bw-extra"><div>' + esc(e.name) + '<br><small>' + money(e.unit_price) + ' each</small></div>' +
        '<span class="bw-mini" data-ex="' + esc(e.key) + '"><button type="button" data-d="-1">\u2212</button><span>' + q + '</span><button type="button" data-d="1">+</button></span></div>';
    }).join('');
  }
  $('#bwGrid').addEventListener('click', function (ev) {
    var incBtn = ev.target.closest('[data-inc]');
    if (incBtn) {
      var ul = incBtn.nextElementSibling, open = ul.hidden;
      ul.hidden = !open; incBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      incBtn.querySelector('span').textContent = open ? '\u2212' : '+';
      return;
    }
    if (ev.target.closest('.bw-inc')) return;
    var card = ev.target.closest('.bw-card'); if (!card) return;
    var key = card.getAttribute('data-key'), step = ev.target.closest('[data-d]');
    if (step) {
      ev.stopPropagation();
      var q = (state.items[key] || state.guests) + (+step.getAttribute('data-d'));
      state.items[key] = Math.max(1, Math.min(state.guests, q));
      card.querySelector('[data-q]').textContent = state.items[key];
    } else {
      if (state.items[key] != null) { delete state.items[key]; }
      else {
        var it0 = ITEM[key];
        if (it0.flat && !tierFor(it0, state.guests)) { return partyTooBig(it0); }
        var clash = false;
        Object.keys(state.items).forEach(function (k) {
          var o = ITEM[k];
          if (o && o.service === it0.service && (it0.flat || o.flat)) { delete state.items[k]; clash = true; }
        });
        state.items[key] = it0.flat ? 1 : state.guests;
        if (clash) { renderStep2(); renderBar(); return; }
      }
      card.classList.toggle('sel', state.items[key] != null);
      var qe = card.querySelector('[data-q]'); if (qe) qe.textContent = state.items[key] || state.guests;
    }
    updateCounts(); renderBar();
  });
  $('#bwGrid').addEventListener('keydown', function (ev) {
    if ((ev.key === 'Enter' || ev.key === ' ') && ev.target.classList.contains('bw-card')) { ev.preventDefault(); ev.target.click(); }
  });
  $('#bwExtras').addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-d]'); if (!b) return;
    var wrap = b.closest('[data-ex]'), key = wrap.getAttribute('data-ex');
    var q = Math.max(0, Math.min(100, (state.extras[key] || 0) + (+b.getAttribute('data-d'))));
    if (q) state.extras[key] = q; else delete state.extras[key];
    wrap.querySelector('span').textContent = q;
    renderBar();
  });

  /* ---------------- step 3: date & time ---------------- */
  var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  var DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  function dateLabel(iso8) { var p = iso8.split('-'), d = new Date(+p[0], +p[1] - 1, +p[2]); return DAYS[d.getDay()] + ', ' + MONTHS[d.getMonth()].slice(0, 3) + ' ' + d.getDate(); }
  function timeLabel(t) { var h = +t.slice(0, 2), m = t.slice(3, 5); return ((h % 12) || 12) + ':' + m + ' ' + (h >= 12 ? 'PM' : 'AM'); }
  function durationHours() {
    var d = 1;
    for (var k in state.items) {
      var it = ITEM[k]; if (!it) continue;
      if (it.package === 'walkin_allday') return null;
      if (it.package === 'walkin_2hour' || it.package === 'group_bundle_2hour') d = Math.max(d, 2);
      if (it.flat) d = Math.max(d, 3);
    }
    return d;
  }
  function renderStep3() {
    var m = state.month, t0 = today(), maxD = new Date(t0.getFullYear(), t0.getMonth(), t0.getDate() + 90);
    if (state.date) { var sp = state.date.split('-'); if (dayBlocked(new Date(+sp[0], +sp[1] - 1, +sp[2]))) { state.date = null; state.time = null; } }
    $('#bwMonth').textContent = MONTHS[m.getMonth()] + ' ' + m.getFullYear();
    $('#bwPrev').disabled = m <= firstOfMonth(t0);
    $('#bwNextM').disabled = new Date(m.getFullYear(), m.getMonth() + 1, 1) > maxD;
    var offset = m.getDay(), last = new Date(m.getFullYear(), m.getMonth() + 1, 0).getDate(), html = '';
    for (var i = 0; i < offset; i++) html += '<span></span>';
    for (var d = 1; d <= last; d++) {
      var cell = new Date(m.getFullYear(), m.getMonth(), d), v = iso(cell);
      var cls = 'bw-day' + (isWeekend(cell) ? ' wk' : '') + (cell.getTime() === t0.getTime() ? ' today' : '') + (state.date === v ? ' sel' : '');
      html += '<button type="button" class="' + cls + '" data-date="' + v + '"' + (cell < t0 || cell > maxD || dayBlocked(cell) ? ' disabled' : '') + '>' + d + '</button>';
    }
    $('#bwDays').innerHTML = html;
    renderSlots();
  }
  function renderSlots() {
    var box = $('#bwSlots');
    if (!state.date) { box.innerHTML = '<div class="bw-empty"><b>\uD83D\uDDD3\uFE0F</b>Pick a day to see time slots</div>'; return; }
    var p = state.date.split('-'), day = new Date(+p[0], +p[1] - 1, +p[2]);
    var open = isWeekend(day) ? 10 : 9, close = 21, dur = durationHours();
    var lastStart = dur === null ? close - 1 : close - dur;
    var now = new Date(), isToday = day.getTime() === today().getTime(), groups = { Morning: [], Afternoon: [], Evening: [] };
    for (var h = open; h <= lastStart; h++) {
      [0, 30].forEach(function (mi) {
        if (h === lastStart && mi > 0) return;
        if (isToday) { var s = new Date(day); s.setHours(h, mi, 0, 0); if (s <= now) return; }
        var v = pad(h) + ':' + pad(mi);
        groups[h < 12 ? 'Morning' : h < 17 ? 'Afternoon' : 'Evening'].push(v);
      });
    }
    var out = '', any = false;
    Object.keys(groups).forEach(function (g) {
      if (!groups[g].length) return; any = true;
      out += '<div class="bw-slots-h">' + g + '</div><div class="bw-slots">' +
        groups[g].map(function (v) { return '<button type="button" class="bw-slot' + (state.time === v ? ' sel' : '') + '" data-time="' + v + '">' + timeLabel(v) + '</button>'; }).join('') + '</div>';
    });
    box.innerHTML = any ? out : '<div class="bw-empty"><b>\uD83D\uDE34</b>No more slots today \u2014 try another day!</div>';
  }
  $('#bwPrev').addEventListener('click', function () { state.month = new Date(state.month.getFullYear(), state.month.getMonth() - 1, 1); renderStep3(); });
  $('#bwNextM').addEventListener('click', function () { state.month = new Date(state.month.getFullYear(), state.month.getMonth() + 1, 1); renderStep3(); });
  $('#bwDays').addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-date]'); if (!b || b.disabled) return;
    state.date = b.getAttribute('data-date'); state.time = null;
    $$('.bw-day').forEach(function (x) { x.classList.toggle('sel', x === b); });
    renderSlots(); renderBar();
  });
  $('#bwSlots').addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-time]'); if (!b) return;
    state.time = b.getAttribute('data-time');
    $$('.bw-slot').forEach(function (x) { x.classList.toggle('sel', x === b); });
    renderBar();
  });

  /* ---------------- step 4: pay ---------------- */
  function renderStep4() {
    var p = price(), h = '<h4>\uD83E\uDDFE Your adventure</h4>';
    h += '<div class="bw-row"><span>\uD83D\uDCC5 ' + esc(dateLabel(state.date)) + ' \u00B7 ' + esc(timeLabel(state.time)) + '<small>' + plural(state.guests, 'guest') + '</small></span><span></span></div>';
    p.lines.forEach(function (l) {
      h += '<div class="bw-row"><span>' + esc(l.it.name) + '<small>' + (l.it.flat ? (l.tier ? 'Up to ' + l.tier.pax + ' guests' : '') : l.qty + ' \u00D7 ' + money(l.it.unit_price)) + (l.free ? ' \u00B7 \uD83C\uDF81 ' + l.free + ' free' : '') + '</small></span><span>' + money(l.gross) + '</span></div>';
    });
    p.extras.forEach(function (x) {
      h += '<div class="bw-row"><span>' + esc(x.e.name) + '<small>' + x.qty + ' \u00D7 ' + money(x.e.unit_price) + '</small></span><span>' + money(x.total) + '</span></div>';
    });
    if (p.bundle) h += '<div class="bw-row disc"><span>\uD83C\uDF81 Buy 4, get 1 free</span><span>\u2212' + money(p.bundle) + '</span></div>';
    if (p.groupDisc) h += '<div class="bw-row disc"><span>\uD83C\uDF89 Group deal (' + p.pct + '%)</span><span>\u2212' + money(p.groupDisc) + '</span></div>';
    h += '<div class="bw-row sum"><span>Total</span><span>' + money(p.total) + '</span></div>';
    $('#bwReceipt').innerHTML = h;
    $('#bwAmount').textContent = money(p.total);
    $('#bwErr').hidden = true;
  }
  var drop = $('#bwDrop'), fileIn = $('#bwFile');
  drop.addEventListener('click', function () { fileIn.click(); });
  ['dragenter', 'dragover'].forEach(function (e) { drop.addEventListener(e, function (ev) { ev.preventDefault(); drop.classList.add('over'); }); });
  ['dragleave', 'drop'].forEach(function (e) { drop.addEventListener(e, function (ev) { ev.preventDefault(); drop.classList.remove('over'); }); });
  drop.addEventListener('drop', function (ev) { if (ev.dataTransfer.files[0]) takeFile(ev.dataTransfer.files[0]); });
  fileIn.addEventListener('change', function () { if (fileIn.files[0]) takeFile(fileIn.files[0]); });
  function showErr(msg) { var e = $('#bwErr'); e.textContent = msg; e.hidden = false; }
  function takeFile(f) {
    if (f.size > 5 * 1024 * 1024) return showErr('That file is over 5MB. Please upload a smaller screenshot.');
    if (!/^image\/|application\/pdf/.test(f.type)) return showErr('Please upload an image (JPG, PNG, WEBP) or a PDF.');
    $('#bwErr').hidden = true; state.file = f;
    var html = '<b>\u2705 Got it!</b><span class="bw-fname">' + esc(f.name) + '</span>';
    if (/^image\//.test(f.type)) html += '<img alt="Payment proof preview" src="' + URL.createObjectURL(f) + '">';
    html += '<small style="display:block;margin-top:8px">Tap to change</small>';
    drop.innerHTML = html; renderBar();
  }
  function submit() {
    var p = price(), fd = new FormData();
    fd.append('party_type', state.party); fd.append('guests', state.guests);
    fd.append('items', JSON.stringify(Object.keys(state.items).map(function (k) { return { key: k, qty: state.items[k] }; })));
    fd.append('extras', JSON.stringify(Object.keys(state.extras).map(function (k) { return { key: k, qty: state.extras[k] }; })));
    fd.append('visit_date', state.date); fd.append('visit_time', state.time); fd.append('receipt', state.file);
    state.busy = true; btnNext.innerHTML = '<span class="bw-spin"></span>Sending\u2026'; btnNext.disabled = true;
    fetch(BW.urls.submit, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': BW.csrf, 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, status: r.status, j: j }; }, function () { return { ok: false, status: r.status, j: {} }; }); })
      .then(function (res) {
        state.busy = false;
        if (res.status === 401) { window.location.href = BW.urls.login; return; }
        if (!res.ok) {
          var msg = res.j.message || 'Something went wrong. Please try again.';
          if (res.j.errors) msg = res.j.errors[Object.keys(res.j.errors)[0]][0];
          showErr(msg); renderBar(); return;
        }
        state.booking = res.j.booking; go(5, 'fwd');
      })
      .catch(function () { state.busy = false; showErr('Network problem \u2014 please check your connection and try again.'); renderBar(); });
    void p;
  }

  /* ---------------- step 5: voucher ---------------- */
  var CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  function fakeCode() { var s = ''; for (var i = 0; i < 8; i++) s += CHARS.charAt(Math.floor(Math.random() * CHARS.length)); return s; }
  function renderStep5() {
    var b = state.booking; if (!b) return;
    var rows = b.items.map(function (i) {
      return '<div class="bw-row"><span>' + esc(i.name) + '<small>' + plural(i.qty, 'guest') + (i.free ? ' \u00B7 \uD83C\uDF81 ' + i.free + ' free' : '') + '</small></span><span>' + money(i.total) + '</span></div>';
    }).join('') + b.extras.map(function (e) {
      return '<div class="bw-row"><span>' + esc(e.name) + '<small>\u00D7 ' + e.qty + '</small></span><span>' + money(e.total) + '</span></div>';
    }).join('');
    var disc = b.discount ? '<div class="bw-row disc"><span>\uD83C\uDF89 Deals applied</span><span>\u2212' + money(b.discount) + '</span></div>' : '';
    var locked = !b.approved;
    $('#bwDone').innerHTML =
      '<div class="bw-ticket"><div class="bw-ticket-top"><small>WONDERPARK ADMIT PASS</small><h3>' + esc(b.reference) + '</h3>' +
      '<div style="opacity:.9;font-size:13px;margin-top:4px">\uD83D\uDCC5 ' + esc(b.date_label) + (b.time_label ? ' \u00B7 ' + esc(b.time_label) : '') + ' \u00B7 ' + plural(b.guests, 'guest') + '</div></div>' +
      '<div class="bw-ticket-mid">' + rows + disc + '<div class="bw-row sum"><span>Total</span><span>' + money(b.total) + '</span></div></div>' +
      '<div class="bw-perf"></div><div class="bw-ticket-bot"><div class="bw-vlabel">YOUR VOUCHER CODE</div>' +
      '<div class="bw-vwrap"><div class="bw-vcode' + (locked ? '' : ' reveal') + '" id="bwCode" aria-live="polite">' + esc(locked ? fakeCode() : b.voucher_code) + '</div>' +
      '<div class="bw-lock' + (locked ? '' : ' gone') + '" id="bwLock">\uD83D\uDD12</div></div>' +
      '<div><span class="bw-pill" id="bwPill"></span></div><div id="bwMsg" style="font-size:13px;color:var(--bw-sub);margin-top:4px"></div>' +
      '<div class="bw-line" id="bwLine"><div class="on">Submitted</div><div id="bwL2">In review</div><div id="bwL3">Approved</div></div></div></div>' +
      '<div class="bw-actions" id="bwActions"></div>';
    paintStatus(b, false);
    if (!b.approved && !b.rejected) startPoll();
  }
  function paintStatus(b, confetti) {
    var pill = $('#bwPill'), msg = $('#bwMsg'), code = $('#bwCode'), lock = $('#bwLock');
    if (!pill) return;
    var l2 = $('#bwL2'), l3 = $('#bwL3'), acts = $('#bwActions');
    var waiver = '<a class="bw-btn go" href="' + BW.urls.waiver + '">\u270D\uFE0F Sign your waiver</a>';
    var mine = '<a class="bw-btn ghost" href="' + BW.urls.bookings + '">My bookings</a>';
    var again = '<a class="bw-btn ghost" href="' + BW.urls.booking + '">Book another \uD83C\uDFA1</a>';
    l2.className = ''; l3.className = '';
    if (b.approved) {
      pill.className = 'bw-pill ok'; pill.textContent = 'Approved! See you at the park \uD83C\uDF89';
      msg.textContent = 'Show this code at the entrance. Please sign the waiver before your visit.';
      l2.className = 'on'; l3.className = 'on';
      code.textContent = b.voucher_code; code.classList.add('reveal'); lock.classList.add('gone');
      acts.innerHTML = waiver + '<button type="button" class="bw-btn ghost" id="bwCopy">Copy code</button><a class="bw-btn ghost" href="' + BW.urls.receipt.replace('__ID__', b.id) + '">View receipt</a>' + mine + again;
      var cp = $('#bwCopy'); if (cp) cp.addEventListener('click', function () { if (navigator.clipboard) navigator.clipboard.writeText(b.voucher_code); cp.textContent = 'Copied \u2713'; });
      if (confetti) boom();
    } else if (b.rejected) {
      pill.className = 'bw-pill bad'; pill.textContent = 'Payment not accepted';
      msg.textContent = b.rejection_reason || 'Please upload a clearer proof of payment.';
      l2.className = 'on';
      acts.innerHTML = '<a class="bw-btn go" href="' + BW.urls.review.replace('__ID__', b.id) + '">Upload new proof</a>' + mine + again;
    } else {
      pill.className = 'bw-pill'; pill.textContent = 'In review \u2014 hang tight!';
      msg.textContent = 'Our team is checking your payment. Your voucher unlocks here as soon as it is approved. \u2728';
      l2.className = 'on cur';
      acts.innerHTML = waiver + '<button type="button" class="bw-btn ghost" id="bwCheck">Check now</button>' + mine + again;
      var ck = $('#bwCheck'); if (ck) ck.addEventListener('click', function () { ck.textContent = 'Checking\u2026'; poll(true); });
    }
  }
  function poll(manual) {
    if (!state.booking) return;
    fetch(BW.urls.status.replace('__ID__', state.booking.id), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (j) {
        if (!j) return;
        var changed = j.status !== state.booking.status;
        state.booking = j;
        if (changed || j.approved || j.rejected) { paintStatus(j, j.approved); if (j.approved || j.rejected) stopPoll(); }
        else if (manual) { var ck = $('#bwCheck'); if (ck) ck.textContent = 'Still in review \u2014 check again soon'; }
      }).catch(function () {});
  }
  function startPoll() { stopPoll(); state.poll = setInterval(poll, 6000); }
  function stopPoll() { if (state.poll) { clearInterval(state.poll); state.poll = null; } }

  /* ---------------- confetti ---------------- */
  function boom() {
    var c = document.createElement('canvas'); c.className = 'bw-confetti'; document.body.appendChild(c);
    var ctx = c.getContext('2d'), W = c.width = window.innerWidth, H = c.height = window.innerHeight;
    var cols = ['#FFC948', '#B82850', '#12B5A6', '#F2762E', '#7A4CE0', '#fff'];
    var ps = []; for (var i = 0; i < 140; i++) ps.push({ x: W / 2, y: H * 0.35, vx: (Math.random() - .5) * 16, vy: Math.random() * -14 - 3, s: 5 + Math.random() * 6, r: Math.random() * 6, vr: (Math.random() - .5) * .4, c: cols[i % cols.length] });
    var t0 = performance.now();
    (function frame(t) {
      ctx.clearRect(0, 0, W, H);
      ps.forEach(function (p) { p.vy += .35; p.x += p.vx; p.y += p.vy; p.r += p.vr; ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.r); ctx.fillStyle = p.c; ctx.fillRect(-p.s / 2, -p.s / 3, p.s, p.s * .6); ctx.restore(); });
      if (t - t0 < 3200) requestAnimationFrame(frame); else c.remove();
    })(t0);
  }

  /* ---------------- boot ---------------- */
  renderTrack(); renderBar();
  if (BW.resume) { state.booking = BW.resume; state.step = 5; go(5, 'fwd'); }
})();
</script>
@endpush
