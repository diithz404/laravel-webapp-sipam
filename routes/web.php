<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KwitansiController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RtController as AdminRtController;
use App\Http\Controllers\Admin\PelangganController as AdminPelangganController;
use App\Http\Controllers\Admin\TarifController as AdminTarifController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Petugas\InputMeterController as PetugasInputMeterController;
use App\Http\Controllers\Petugas\PembayaranController as PetugasPembayaranController;
use App\Http\Controllers\Petugas\PelangganController as PetugasPelangganController;

// Public Routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Shared Kwitansi Struk View
Route::get('/kwitansi/{catatanMeter}', [KwitansiController::class, 'show'])->name('kwitansi.show');

// Super Admin Panel Routes (Middleware: role:admin)
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Master Data RT
    Route::get('/rt', [AdminRtController::class, 'index'])->name('rt.index');
    Route::post('/rt', [AdminRtController::class, 'store'])->name('rt.store');
    Route::put('/rt/{rt}', [AdminRtController::class, 'update'])->name('rt.update');
    Route::delete('/rt/{rt}', [AdminRtController::class, 'destroy'])->name('rt.destroy');

    // Master Data Pelanggan
    Route::get('/pelanggan', [AdminPelangganController::class, 'index'])->name('pelanggan.index');
    Route::post('/pelanggan', [AdminPelangganController::class, 'store'])->name('pelanggan.store');
    Route::get('/pelanggan/{pelanggan}', [AdminPelangganController::class, 'show'])->name('pelanggan.show');
    Route::put('/pelanggan/{pelanggan}', [AdminPelangganController::class, 'update'])->name('pelanggan.update');
    Route::delete('/pelanggan/{pelanggan}', [AdminPelangganController::class, 'destroy'])->name('pelanggan.destroy');

    // Master Tarif & Tiers
    Route::get('/tarif', [AdminTarifController::class, 'index'])->name('tarif.index');
    Route::post('/tarif', [AdminTarifController::class, 'store'])->name('tarif.store');

    // Rekap & Laporan
    Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export-csv', [AdminLaporanController::class, 'exportCsv'])->name('laporan.export');

    // Kelola User
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

// Petugas RT Panel Routes (Middleware: role:petugas,admin)
Route::middleware(['role:petugas,admin'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');

    // Input Meter Fast Interface
    Route::get('/input-meter', [PetugasInputMeterController::class, 'index'])->name('input-meter.index');
    Route::post('/input-meter/single', [PetugasInputMeterController::class, 'storeSingle'])->name('input-meter.single');
    Route::post('/input-meter/batch', [PetugasInputMeterController::class, 'storeBatch'])->name('input-meter.batch');
    Route::post('/input-meter/tutup-periode', [PetugasInputMeterController::class, 'tutupPeriodeRt'])->name('input-meter.tutup-periode');

    // Kasir & Pembayaran
    Route::get('/pembayaran', [PetugasPembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran', [PetugasPembayaranController::class, 'store'])->name('pembayaran.store');

    // Data Warga / Pelanggan RT
    Route::get('/warga', [PetugasPelangganController::class, 'index'])->name('warga.index');
});
