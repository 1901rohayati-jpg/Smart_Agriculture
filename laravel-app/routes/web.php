<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceSettingsController;
use App\Http\Controllers\HistoryExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['device.session'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::prefix('device')->name('device.')->middleware('throttle:30,1')->group(function () {
        Route::put('/plant-name', [DeviceSettingsController::class, 'updatePlantName'])->name('plant-name.update');
        Route::put('/schedule', [DeviceSettingsController::class, 'updateSchedule'])->name('schedule.update');
        Route::put('/wifi', [DeviceSettingsController::class, 'updateWifi'])->name('wifi.update');
        Route::post('/manual-trigger', [DeviceSettingsController::class, 'manualTrigger'])->name('manual-trigger');
    });

    Route::get('/history/export', [HistoryExportController::class, 'export'])->name('history.export');
});
