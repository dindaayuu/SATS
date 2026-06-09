<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScanController;

Route::get('/dashboard', [ScanController::class, 'dashboard']);

Route::post('/pickup/scan', [ScanController::class, 'pickupScan']);

Route::post('/pickup/save', [ScanController::class, 'pickupSave']);

Route::post('/return/scan-bag', [ScanController::class, 'returnScanBag']);

Route::post('/return/scan-item', [ScanController::class, 'returnScanItem']);

Route::post('/return/save', [ScanController::class, 'returnSave']);

Route::post('/pickup/validate', [ScanController::class, 'validatePickup']);

Route::get('/scan-mode', [ScanController::class, 'getScanMode']);

Route::get('/return/details/{bag}', [ScanController::class, 'getBagDetails']);