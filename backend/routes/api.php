<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\ReportingController;

Route::get('/dashboard', [ScanController::class, 'dashboard']);

Route::post('/pickup/scan', [ScanController::class, 'pickupScan']);

Route::post('/pickup/save', [ScanController::class, 'pickupSave']);

Route::post('/return/scan-bag', [ScanController::class, 'returnScanBag']);

Route::post('/return/scan-item', [ScanController::class, 'returnScanItem']);

Route::post('/return/save', [ScanController::class, 'returnSave']);

Route::post('/pickup/validate', [ScanController::class, 'validatePickup']);

Route::get('/scan-mode', [ScanController::class, 'getScanMode']);

Route::get('/return/details/{bag}', [ScanController::class, 'getBagDetails']);

Route::get('/test-asset/{code}',[ScanController::class, 'testAsset']);

Route::apiResource('tenants', TenantController::class)->only([
    'index',
    'show',
    'update',
    'store'
]);

Route::prefix('reporting')->group(function () {
    Route::get('/summary', [ReportingController::class, 'summary']);

    Route::get(
        '/activity-chart',
        [ReportingController::class, 'activityChart']
    );

    Route::get(
        '/top-stores',
        [ReportingController::class, 'topStores']
    );

    Route::get(
        '/problematic-devices',
        [ReportingController::class,
        'problematicDevices']
    );

    Route::get(
        '/unreturned-devices',
        [ReportingController::class,
        'unreturnedDevices']
    );

    Route::get(
        '/dashboard-history',
        [ReportingController::class,
        'dashboardHistory']
    );

    Route::get(
        '/transactions',
        [ReportingController::class, 'transactions']
    );
});

