<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\MlForecastController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\BookingController as UserBookingController;
use App\Http\Controllers\User\WaiverController as UserWaiverController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ChatMessagingController;
use App\Http\Controllers\VisitorSummaryController;
use App\Http\Controllers\ZoneForecastController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\BookingCmsController;
use App\Http\Controllers\EmployeeController;

/*
|--------------------------------------------------------------------------
| Landing page (CMS-driven)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('landing', [
        'hero'    => \App\Models\SiteContent::section('hero'),
        'split'   => \App\Models\SiteContent::section('split'),
        'contact' => \App\Models\SiteContent::section('contact'),
        'footer'  => \App\Models\SiteContent::section('footer'),

        'passes'      => \App\Models\SiteCard::type('pass')->active()->ordered()->get(),
        'attractions' => \App\Models\SiteCard::type('attraction')->active()->ordered()->get(),
        'services'    => \App\Models\SiteCard::type('service')->active()->ordered()->get(),
        'steps'       => \App\Models\SiteCard::type('step')->active()->ordered()->get(),
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Two-factor authentication
Route::get('/two-factor', [AuthController::class, 'showTwoFactorForm'])->name('two-factor.show');
Route::post('/two-factor', [AuthController::class, 'verifyTwoFactor'])->name('two-factor.verify');
Route::post('/two-factor/resend', [AuthController::class, 'resendTwoFactor'])->name('two-factor.resend');

// Google OAuth
Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

// Facebook OAuth
Route::get('/auth/facebook/redirect', [FacebookController::class, 'redirect'])->name('facebook.redirect');
Route::get('/auth/facebook/callback', [FacebookController::class, 'callback'])->name('facebook.callback');

/*
|--------------------------------------------------------------------------
| User app (customer-facing) — Account, Waiver, Booking, My Bookings
|--------------------------------------------------------------------------
*/
Route::prefix('app')
    ->name('user.')
    ->middleware('auth.session')
    ->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::post('/account', [UserDashboardController::class, 'update'])->name('account.update');
        Route::post('/account/two-factor', [UserDashboardController::class, 'toggleTwoFactor'])->name('account.two-factor.toggle');

        Route::get('/waiver', [UserWaiverController::class, 'show'])->name('waiver');
        Route::post('/waiver', [UserWaiverController::class, 'store'])->name('waiver.store');
        Route::get('/bookings/{booking}/waiver', [UserWaiverController::class, 'show'])->name('bookings.waiver');
        Route::post('/bookings/{booking}/waiver', [UserWaiverController::class, 'store'])->name('bookings.waiver.store');

        Route::get('/booking', [UserBookingController::class, 'create'])->name('booking');
        Route::post('/booking', [UserBookingController::class, 'store'])->name('booking.store');

        Route::get('/my-bookings', [UserBookingController::class, 'index'])->name('bookings');

        Route::post('/bookings/{booking}/cancel', [UserBookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/reschedule', [UserBookingController::class, 'reschedule'])->name('bookings.reschedule');
        Route::get('/bookings/{booking}/reschedule', [UserBookingController::class, 'editReschedule'])->name('bookings.reschedule.edit');
        Route::get('/bookings/{booking}/review', [UserBookingController::class, 'review'])->name('bookings.review');
        Route::post('/bookings/{booking}/payment', [UserBookingController::class, 'confirmPayment'])->name('bookings.payment');
    });

Route::get('/app/bookings/{booking}/receipt', [BookingController::class, 'receipt'])
    ->name('user.bookings.receipt');

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/
Route::get('/home', [HomeController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Inventory
|--------------------------------------------------------------------------
*/
Route::get('/inventory', [InventoryController::class, 'index']);
Route::post('/inventory/add', [InventoryController::class, 'store']);
Route::post('/inventory/update', [InventoryController::class, 'update']);
Route::post('/inventory/delete', [InventoryController::class, 'destroy']);
Route::post('/inventory/restock', [InventoryController::class, 'restock']);
Route::post('/inventory/import', [InventoryController::class, 'import']);
Route::post('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
Route::post('/inventory/add-category', [InventoryController::class, 'storeCategory'])->name('inventory.addCategory');

/*
|--------------------------------------------------------------------------
| POS
|--------------------------------------------------------------------------
*/
Route::get('/pos', [PosController::class, 'index']);
Route::get('/pos/get_cashflow', [PosController::class, 'getCashflow']);
Route::get('/pos/open-drawer', [PosController::class, 'openDrawer']);
Route::get('/pos/get_transaction', [PosController::class, 'getTransaction']);
Route::get('/pos/voucher-lookup', [PosController::class, 'lookupVoucher']);
Route::post('/pos/verify_manager', [PosController::class, 'verifyManager']);
Route::post('/pos/save-transaction', [PosController::class, 'saveTransaction']);
Route::post('/pos/log-void', [PosController::class, 'logVoid']);
Route::post('/pos/void-transaction', [PosController::class, 'voidTransaction']);
Route::post('/pos/reprint', [PosController::class, 'reprintTransaction'])->name('pos.reprint');
Route::post('/pos/cash-movement', [PosController::class, 'addCashMovement']);
Route::post('/pos/print-sales-report', [PosController::class, 'printSalesReportData']);
Route::get('/pos/present-report', [PosController::class, 'presentReport']);
Route::get('/pos/historical-cutoffs', [PosController::class, 'historicalCutoffs']);
Route::get('/pos/cutoff/{id}', [PosController::class, 'cutoffDetail']);
Route::get('/pos/cutoff-status', [PosController::class, 'cutoffStatus']);

/*
|--------------------------------------------------------------------------
| Reports / Analytics / ML Forecast
|--------------------------------------------------------------------------
*/
Route::get('/reports/present', [AnalyticsController::class, 'reportPresent']);
Route::get('/reports/historical', [AnalyticsController::class, 'reportHistorical']);
Route::get('/reports/bir', [AnalyticsController::class, 'reportBir']);

Route::get('/analytics', [AnalyticsController::class, 'index']);
Route::get('/analytics/live-count', [VisitorSummaryController::class, 'liveCount'])->name('analytics.live-count');

Route::get('/ml-forecast', [MlForecastController::class, 'index']);
Route::get('/api/ml/summary', [MlForecastController::class, 'summary']);
Route::get('/api/ml/top_products', [MlForecastController::class, 'topProducts']);
Route::get('/api/ml/day_of_week', [MlForecastController::class, 'dayOfWeek']);
Route::get('/api/ml/hourly_peaks', [MlForecastController::class, 'hourlyPeaks']);
Route::get('/api/ml/next_month_forecast', [MlForecastController::class, 'nextMonthForecast']);
Route::get('/api/ml/monthly_trend', [MlForecastController::class, 'monthlyTrend']);

// Zone Forecast pages
Route::get('/fieldOfRides', [ZoneForecastController::class, 'index'])
    ->defaults('zone', 'Field of Rides')
    ->name('zone.fieldOfRides');

Route::get('/RollerFever', [ZoneForecastController::class, 'index'])
    ->defaults('zone', 'Roller Fever')
    ->name('zone.rollerFever');

Route::get('/DinoAdventure', [ZoneForecastController::class, 'index'])
    ->defaults('zone', 'Dino Adventure')
    ->name('zone.dinoAdventure');

/*
|--------------------------------------------------------------------------
| Customers / Reservations / Chat
|--------------------------------------------------------------------------
*/
Route::get('/customer', [VisitorSummaryController::class, 'index'])->name('visitor-summary');

Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
Route::put('/reservations/{reservation}', [ReservationController::class, 'update'])->name('reservations.update');
Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');

Route::resource('chatmessaging', ChatMessagingController::class);

/*
|--------------------------------------------------------------------------
| Manpower — Employees
|--------------------------------------------------------------------------
*/
Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');
Route::match(['post', 'delete'], '/employees/clear-all', [EmployeeController::class, 'clearAll'])->name('employees.clear-all');
Route::match(['post', 'put', 'patch'], '/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
Route::match(['post', 'patch', 'put'], '/employees/{employee}/deactivate', [EmployeeController::class, 'deactivate'])->name('employees.deactivate');
Route::match(['post', 'patch', 'put'], '/employees/{employee}/activate', [EmployeeController::class, 'activate'])->name('employees.activate');

/*
|--------------------------------------------------------------------------
| Manpower — Attendance, Salary, Payroll
|--------------------------------------------------------------------------
*/
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
Route::get('/attendance/checkin', [AttendanceController::class, 'showCheckin'])->name('attendance.checkin');
Route::post('/attendance/checkin', [AttendanceController::class, 'storeCheckin'])->name('attendance.checkin.store');
Route::post('/attendance/store', [AttendanceController::class, 'storeAttendance'])->name('attendance.store');
Route::post('/attendance/import', [AttendanceController::class, 'importAttendance'])->name('attendance.import');
Route::post('/attendance/time-in', [AttendanceController::class, 'timeIn']);
Route::post('/attendance/time-out', [AttendanceController::class, 'timeOut']);

Route::get('/attendance/salary', [AttendanceController::class, 'salary'])->name('salary');
Route::post('/attendance/salary/rates', [AttendanceController::class, 'updateRates'])->name('salary.rates');
Route::post('/attendance/salary/store', [AttendanceController::class, 'storePayroll'])->name('salary.store');

Route::get('/attendance/payroll-history', [AttendanceController::class, 'payrollHistory'])->name('payroll-history');
Route::delete('/attendance/payroll-history/{batch}', [AttendanceController::class, 'deleteBatch'])->name('payroll-history.delete-batch');

Route::get('/attendance/payslip', [AttendanceController::class, 'payslip'])->name('payslip');
Route::get('/attendance/payslip/{id}', [AttendanceController::class, 'payslip'])->name('payslip.show');

/*
|--------------------------------------------------------------------------
| Accounts
|--------------------------------------------------------------------------
*/
Route::get('/accounts', [AccountController::class, 'index']);
Route::get('/accounts/create', [AccountController::class, 'create']);
Route::post('/accounts/store', [AccountController::class, 'store']);
Route::put('/accounts/update/{id}', [AccountController::class, 'update']);
Route::delete('/accounts/delete/{id}', [AccountController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

/*
|--------------------------------------------------------------------------
| Notifications
| (static URIs like /bulk-destroy, /bulk-read, /poll must stay above
|  the {notification} wildcard routes)
|--------------------------------------------------------------------------
*/
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
Route::patch('/notifications/bulk-read', [NotificationController::class, 'bulkMarkRead'])->name('notifications.bulk-read');
Route::delete('/notifications/bulk-destroy', [NotificationController::class, 'bulkDestroy'])->name('notifications.bulk-destroy');
Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

/*
|--------------------------------------------------------------------------
| CMS (admin/cms)
|--------------------------------------------------------------------------
*/
Route::prefix('admin/cms')->name('cms.')->group(function () {

    Route::get('/', [CmsController::class, 'index'])->name('index');

    // Simple text sections: hero, split, contact, footer
    Route::get('/section/{section}', [CmsController::class, 'editSection'])->name('section.edit');
    Route::put('/section/{section}', [CmsController::class, 'updateSection'])->name('section.update');

    // Booking & Pricing
    Route::get('/booking', [BookingCmsController::class, 'edit'])->name('booking.pricing');
    Route::put('/booking', [BookingCmsController::class, 'update'])->name('booking.pricing.update');
    Route::post('/booking/package/{service}/{package}/reset', [BookingCmsController::class, 'resetPackage'])->name('booking.pricing.reset-package');
    Route::post('/booking/addon/{service}/{addon}/reset', [BookingCmsController::class, 'resetAddon'])->name('booking.pricing.reset-addon');

    // Collections
    Route::post('/collections', [CmsController::class, 'storeCollection'])->name('collections.store');
    Route::delete('/collections/{collection:slug}', [CmsController::class, 'destroyCollection'])->name('collections.destroy');

    // Card-based sections: pass, attraction, service, step
    Route::get('/cards/{type}', [CmsController::class, 'cards'])->name('cards');
    Route::get('/cards/{type}/create', [CmsController::class, 'createCard'])->name('cards.create');
    Route::post('/cards/{type}', [CmsController::class, 'storeCard'])->name('cards.store');
    Route::get('/card/{card}/edit', [CmsController::class, 'editCard'])->name('cards.edit');
    Route::put('/card/{card}', [CmsController::class, 'updateCard'])->name('cards.update');
    Route::delete('/card/{card}', [CmsController::class, 'destroyCard'])->name('cards.destroy');
});