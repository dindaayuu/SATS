<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\ReportingController;
use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\DeviceReplacementController;


/*
|--------------------------------------------------------------------------
| SATS DASHBOARD
|--------------------------------------------------------------------------
| Dashboard utama admin SATS
*/

Route::get(
    '/dashboard',
    [
        ScanController::class,
        'dashboard'
    ]
);


/*
|--------------------------------------------------------------------------
| SATS PICKUP
|--------------------------------------------------------------------------
| Proses pengambilan tas tenant
*/

Route::post(
    '/pickup/scan',
    [
        ScanController::class,
        'pickupScan'
    ]
);

Route::post(
    '/pickup/save',
    [
        ScanController::class,
        'pickupSave'
    ]
);

Route::post(
    '/pickup/validate',
    [
        ScanController::class,
        'validatePickup'
    ]
);


/*
|--------------------------------------------------------------------------
| SATS RETURN
|--------------------------------------------------------------------------
| Proses pengembalian tas tenant
*/

Route::post(
    '/return/scan-bag',
    [
        ScanController::class,
        'returnScanBag'
    ]
);

Route::post(
    '/return/scan-item',
    [
        ScanController::class,
        'returnScanItem'
    ]
);

Route::post(
    '/return/save',
    [
        ScanController::class,
        'returnSave'
    ]
);

Route::get(
    '/return/details/{bag}',
    [
        ScanController::class,
        'getBagDetails'
    ]
);


/*
|--------------------------------------------------------------------------
| SATS CONFIG
|--------------------------------------------------------------------------
*/

Route::get(
    '/scan-mode',
    [
        ScanController::class,
        'getScanMode'
    ]
);

Route::get(
    '/test-asset/{code}',
    [
        ScanController::class,
        'testAsset'
    ]
);


/*
|--------------------------------------------------------------------------
| TENANT MANAGEMENT ROUTE MAP
|--------------------------------------------------------------------------
| Data tenant + posisi map admin
*/

Route::apiResource(
    'tenants',
    TenantController::class
)->only([
    'index',
    'show',
    'update',
    'store'
]);


/*
|--------------------------------------------------------------------------
| MOBILE CHECKLIST
|--------------------------------------------------------------------------
| Digunakan aplikasi checklist PIC
*/

Route::prefix('checklist')
    ->group(function(){


    /*
    Ambil dashboard progress checklist
    */

    Route::get(
        '/dashboard',
        [
            ChecklistController::class,
            'dashboard'
        ]
    );


    /*
    List tenant route checklist
    */

    Route::get(
        '/tenants',
        [
            ChecklistController::class,
            'tenants'
        ]
    );


    /*
    Detail tenant
    Support:
    - Bag Detail
    - Tenant Detail
    */

    Route::get(
        '/tenants/{id}',
        [
            ChecklistController::class,
            'detailTenant'
        ]
    );


    /*
    Master jenis kendala
    */

    Route::get(
        '/problem-types',
        [
            ChecklistController::class,
            'problemTypes'
        ]
    );


    /*
    Simpan hasil checklist
    */

    Route::post(
        '/submit',
        [
            ChecklistController::class,
            'submit'
        ]
    );


    /*
    History checklist mobile
    */

    Route::get(
        '/report',
        [
            ChecklistController::class,
            'report'
        ]
    );

});


/*
|--------------------------------------------------------------------------
| DEVICE REPLACEMENT
|--------------------------------------------------------------------------
| Penggantian device ketika ada kendala
*/

Route::post(
    '/device-replacements',
    [
        DeviceReplacementController::class,
        'replace'
    ]
);


/*
|--------------------------------------------------------------------------
| REPORTING CENTER
|--------------------------------------------------------------------------
| Dashboard web reporting
*/

Route::prefix('reporting')
    ->group(function(){


    Route::get(
        '/summary',
        [
            ReportingController::class,
            'summary'
        ]
    );


    Route::get(
        '/activity-chart',
        [
            ReportingController::class,
            'activityChart'
        ]
    );


    Route::get(
        '/top-stores',
        [
            ReportingController::class,
            'topStores'
        ]
    );


    Route::get(
        '/problematic-devices',
        [
            ReportingController::class,
            'problematicDevices'
        ]
    );


    Route::get(
        '/unreturned-devices',
        [
            ReportingController::class,
            'unreturnedDevices'
        ]
    );


    Route::get(
        '/dashboard-history',
        [
            ReportingController::class,
            'dashboardHistory'
        ]
    );


    Route::get(
        '/transactions',
        [
            ReportingController::class,
            'transactions'
        ]
    );


    Route::get(
        '/checklists',
        [
            ReportingController::class,
            'checklistHistory'
        ]
    );

    Route::get(
        '/dashboard-checklist-history',
        [ReportingController::class, 'dashboardChecklistHistory']
    );

});