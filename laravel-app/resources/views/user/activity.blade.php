{{--
    resources/views/user/activity.blade.php
    Route: GET /app/activity -> user.activity
    Controller: App\Http\Controllers\User\ActivityLogController@index
    Expects: $logs (LengthAwarePaginator of system_logs rows), $group (string), $user
--}}
@extends('layouts.user')

@section('title', 'Activity Log')
@section('page-title', 'Activity Log')
@section('page-subtitle', 'Your recent account activity')
@section('body-class', 'page-uniform')

@push('styles')
<style>
    .al-filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;}
    .al-filters .mb-tab{display:inline-flex;align-items:center;text-decoration:none;}
    .al-card{padding:6px 0;}
    .al-card:hover{transform:none;box-shadow:var(--shadow-card);}
    .al-row{display:flex;gap:14px;align-items:flex-start;padding:14px 18px;border-bottom:1px solid var(--line);}
    .al-row:last-child{border-bottom:none;}
    .al-main{flex:1;min-width:0;}
    .al-title{margin:0 0 2px;font-size:14px;font-weight:600;color:var(--ink);word-break:break-word;}
    .al-meta{font-size:12.5px;color:var(--muted);}
    .al-side{flex-shrink:0;text-align:right;}
    .al-time{margin-top:4px;font-size:12px;color:var(--muted);}
    .al-empty{margin:18px;padding:28px 18px;border-radius:12px;background:var(--gray-tile,#F3F3F3);text-align:center;color:var(--ink-soft);font-size:14px;}
    .al-pager{display:flex;justify-content:center;align-items:center;gap:12px;margin-top:16px;}
    .al-pager .pg-btn{display:inline-flex;align-items:center;padding:8px 16px;text-decoration:none;}
    .al-pager .al-disabled{opacity:.45;pointer-events:none;}
    .al-page{font-size:12.5px;color:var(--muted);}
    @media (max-width:640px){
        .al-row{flex-wrap:wrap;}
        .al-side{width:100%;margin-left:54px;display:flex;align-items:center;gap:10px;text-align:left;}
        .al-time{margin-top:0;}
    }
</style>
@endpush

@section('content')
    @php
        $filters = [
            ''         => 'All',
            'auth'     => 'Sign in',
            'booking'  => 'Bookings',
            'waiver'   => 'Waiver',
            'account'  => 'Account',
            'security' => 'Security',
        ];
        $look = [
            'auth'     => ['icon' => 'bi-box-arrow-in-right', 'ico' => 'c-violet', 'badge' => 'info',    'label' => 'Sign in'],
            'booking'  => ['icon' => 'bi-calendar-check',     'ico' => 'c-teal',   'badge' => 'success', 'label' => 'Booking'],
            'waiver'   => ['icon' => 'bi-file-earmark-check', 'ico' => 'c-amber',  'badge' => 'warn',    'label' => 'Waiver'],
            'account'  => ['icon' => 'bi-person-gear',        'ico' => 'c-violet', 'badge' => '',        'label' => 'Account'],
            'security' => ['icon' => 'bi-shield-lock',        'ico' => '',         'badge' => 'danger',  'label' => 'Security'],
        ];
    @endphp

    @if (($group ?? '') !== '' || $logs->total() > 5)
    <div class="al-filters" role="tablist" aria-label="Filter activity">
        @foreach ($filters as $key => $label)
            <a href="{{ route('user.activity', $key === '' ? [] : ['type' => $key]) }}"
               class="mb-tab {{ ($group ?? '') === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
    @endif

    <div class="u-card al-card">
        @forelse ($logs as $log)
            @php
                $action  = $log->action ?? $log->event_type ?? '';
                $g       = preg_split('/[._]/', $action)[0];
                if (in_array($g, ['reservation', 'payment'], true)) {
                    $g = 'booking';
                }
                $v       = $look[$g] ?? $look['account'];
                $failed  = str_contains($action, 'failed');
                $when    = \Carbon\Carbon::parse($log->created_at);
                $ua      = (string) ($log->user_agent ?? '');
                $browser = str_contains($ua, 'Edg/') ? 'Edge'
                    : (str_contains($ua, 'Firefox') ? 'Firefox'
                    : (str_contains($ua, 'Chrome') ? 'Chrome'
                    : (str_contains($ua, 'Safari') ? 'Safari' : 'Browser')));
                $device  = (str_contains($ua, 'Mobile') || str_contains($ua, 'Android') || str_contains($ua, 'iPhone')) ? 'Mobile' : 'Desktop';
                $ip      = $log->ip_address ?? null;
            @endphp
            <div class="al-row">
                <span class="wp-ico {{ $failed ? 'c-danger' : $v['ico'] }}" aria-hidden="true">
                    <i class="bi {{ $failed ? 'bi-exclamation-triangle' : $v['icon'] }}"></i>
                </span>
                <div class="al-main">
                    <p class="al-title">{{ $log->description }}</p>
                    <span class="al-meta">{{ $browser }} · {{ $device }}@if ($ip) · {{ $ip }}@endif</span>
                </div>
                <div class="al-side">
                    @if ($failed)<span class="wp-badge danger">Failed</span>@endif
                    <div class="al-time" title="{{ $when->format('Y-m-d H:i:s') }}">{{ $when->format('M j, Y · g:i A') }}</div>
                </div>
            </div>
        @empty
            <div class="al-empty">No activity yet. Your sign-ins, bookings, and account changes will show up here.</div>
        @endforelse
    </div>

    @if ($logs->hasPages())
        <div class="al-pager">
            @if ($logs->onFirstPage())
                <span class="pg-btn al-disabled">Previous</span>
            @else
                <a class="pg-btn" href="{{ $logs->previousPageUrl() }}">Previous</a>
            @endif
            <span class="al-page">Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}</span>
            @if ($logs->hasMorePages())
                <a class="pg-btn" href="{{ $logs->nextPageUrl() }}">Next</a>
            @else
                <span class="pg-btn al-disabled">Next</span>
            @endif
        </div>
    @endif
@endsection