<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\ReportingController;
use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\DeviceReplacementController;


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

/*
|--------------------------------------------------------------------------
| MOBILE CHECKLIST
|--------------------------------------------------------------------------
*/

Route::prefix('checklist')->group(function () {


    /*
    List tenant
    */
    Route::get(
        '/tenants',
        [
            ChecklistController::class,
            'tenants'
        ]
    );



    /*
    Detail tenant + device
    */
    Route::get(
        '/tenants/{id}',
        [
            ChecklistController::class,
            'detailTenant'
        ]
    );

  /*
    Problem options
    */
    Route::get(
        '/problem-types',
        [
            ChecklistController::class,
            'problemTypes'
        ]
    );

    /*
    Submit checklist
    */
    Route::post(
        '/submit',
        [
            ChecklistController::class,
            'submit'
        ]
    );


});

Route::post(
    '/device-replacements',
    [
        DeviceReplacementController::class,
        'replace'
    ]
);

Route::prefix('report')->group(function () {


    Route::get(
        '/checklists',
        [
            ReportingController::class,
            'checklistHistory'
        ]
    );


});

Route::get(
    '/checklists',
    [
        ReportingController::class,
        'checklistHistory'
    ]
);
Route::get(
    '/checklist/report',
    [
        ChecklistController::class,
        'report'
    ]
);

Route::get(
    '/checklist/dashboard',
    [ChecklistController::class,'dashboard']
);