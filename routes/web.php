<?php

use App\Http\Controllers\Dashboard\KunjunganController as DashboardKunjunganController;
use App\Http\Controllers\Dashboard\TugasController as DashboardTugasController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guest\KunjunganController as GuestKunjunganController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [GuestKunjunganController::class, 'create'])->name('home');
Route::get('/kunjungan/create', [GuestKunjunganController::class, 'create'])
    ->middleware('throttle:20,1')
    ->name('kunjungan.create');
Route::post('/kunjungan', [GuestKunjunganController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('kunjungan.store');
Route::get('/kunjungan/{kode_publik}/status', [GuestKunjunganController::class, 'status'])
    ->name('kunjungan.status');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Role-protected dashboard areas (placeholders for later phases)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard/kunjungan', [DashboardKunjunganController::class, 'index'])->middleware('role:resepsionis,admin')->name('dashboard.kunjungan.index');
    Route::get('/dashboard/kunjungan/{kunjungan}', [DashboardKunjunganController::class, 'show'])->middleware('role:resepsionis,admin,pegawai')->name('dashboard.kunjungan.show');
    Route::patch('/dashboard/kunjungan/{kunjungan}/klasifikasi', [DashboardKunjunganController::class, 'klasifikasi'])->middleware('role:resepsionis,admin')->name('dashboard.kunjungan.klasifikasi');
    Route::patch('/dashboard/kunjungan/{kunjungan}/pegawai', [DashboardKunjunganController::class, 'pegawai'])->middleware('role:resepsionis,admin')->name('dashboard.kunjungan.pegawai');
    Route::patch('/dashboard/kunjungan/{kunjungan}/status', [DashboardKunjunganController::class, 'status'])->middleware('role:resepsionis,admin,pegawai')->name('dashboard.kunjungan.status');
    Route::get('/dashboard/tugas', [DashboardTugasController::class, 'index'])->middleware('role:pegawai')->name('dashboard.tugas.index');
    Route::get('/dashboard/rekap', fn () => Inertia::render('Dashboard'))->middleware('role:resepsionis,admin,pimpinan')->name('dashboard.rekap.index');
    Route::get('/dashboard/ringkasan', fn () => Inertia::render('Dashboard'))->middleware('role:admin,pimpinan')->name('dashboard.ringkasan.index');
    Route::get('/dashboard/units', fn () => Inertia::render('Dashboard'))->middleware('role:resepsionis,admin')->name('dashboard.units.index');
    Route::get('/dashboard/pegawai', fn () => Inertia::render('Dashboard'))->middleware('role:resepsionis,admin')->name('dashboard.pegawai.index');
    Route::get('/dashboard/jam-layanan', fn () => Inertia::render('Dashboard'))->middleware('role:resepsionis,admin')->name('dashboard.jam-layanan.index');
    Route::get('/dashboard/faq', fn () => Inertia::render('Dashboard'))->middleware('role:resepsionis,admin')->name('dashboard.faq.index');
    Route::get('/dashboard/users', fn () => Inertia::render('Dashboard'))->middleware('role:admin')->name('dashboard.users.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
