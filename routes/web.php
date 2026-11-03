<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CrewController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PhotographerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShootController;
use App\Http\Controllers\ShootTypeController;
use Illuminate\Support\Facades\Route;

// --- Authentication & Role Switching ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/login/quick', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/switch-role/{role}', [AuthController::class, 'switchRole'])->name('switch.role');

// --- Protected App Routes ---
Route::middleware(['auth'])->group(function () {
    // --- Dashboard ---
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // --- Studio Operations, Financials, Shoots & Management (Super Admin, Admin & Telecaller) ---
    Route::middleware(['role:super_admin,admin,telecaller'])->group(function () {
        // Customers
        Route::resource('customers', CustomerController::class);

        // Packages
        Route::resource('packages', PackageController::class);
        Route::post('/packages/{package}/toggle', [PackageController::class, 'toggleStatus'])->name('packages.toggle');

        // Shoot Operations & Bookings
        Route::resource('shoots', ShootController::class);
        Route::post('/shoots/{shoot}/crew', [ShootController::class, 'assignCrew'])->name('shoots.crew.assign');
        Route::delete('/shoots/{shoot}/crew/{assignment}', [ShootController::class, 'removeCrew'])->name('shoots.crew.remove');
        Route::post('/shoots/{shoot}/status', [ShootController::class, 'updateStatus'])->name('shoots.status');

        // Leads & Inquiries
        Route::resource('leads', LeadController::class);
        Route::post('/leads/{lead}/activities', [LeadController::class, 'addActivity'])->name('leads.activities.store');
        Route::post('/leads/{lead}/convert', [LeadController::class, 'convertToCustomer'])->name('leads.convert');

        // Payments & Invoices
        Route::resource('payments', PaymentController::class);
        Route::resource('invoices', InvoiceController::class);

        // Reports & Analytics
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        // Studio Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Photographers Directory & Management
        Route::get('/photographers', [PhotographerController::class, 'index'])->name('photographers.index');
        Route::post('/photographers', [PhotographerController::class, 'store'])->name('photographers.store');
        Route::delete('/photographers/{photographer}', [PhotographerController::class, 'destroy'])->name('photographers.destroy');

        // Crew & Support Staff (Telecallers, Operations, Team Management)
        Route::resource('crew', CrewController::class)->only(['index', 'store', 'destroy']);

        // Global Studio Spotlight Search
        Route::get('/search/global', [SearchController::class, 'global'])->name('search.global');
    });

    // --- Admin-Only Studio Master Data & Shoot Types ---
    Route::middleware(['role:super_admin,admin'])->group(function () {
        Route::resource('shoot-types', ShootTypeController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('/shoot-types/{shootType}/toggle', [ShootTypeController::class, 'toggleStatus'])->name('shoot-types.toggle');
    });

    // --- Photographer Portal & Workspace ---
    Route::middleware(['role:super_admin,admin,photographer'])->group(function () {
        Route::get('/my-dashboard', [PhotographerController::class, 'dashboard'])->name('photographers.dashboard');
        Route::get('/my-shoots', [PhotographerController::class, 'portal'])->name('photographers.portal');
        Route::get('/my-payments', [PhotographerController::class, 'payments'])->name('photographers.payments');
        Route::get('/my-shoots/{shoot}/invoice', [PhotographerController::class, 'invoice'])->name('photographers.shoots.invoice');
        Route::post('/shoots/{shoot}/delivery', [PhotographerController::class, 'submitDelivery'])->name('photographers.delivery');
    });
});

// --- Client Gallery Portal (Public) ---
Route::get('/portal/gallery/{shootNumber}', [InvoiceController::class, 'clientGallery'])->name('portal.gallery');
