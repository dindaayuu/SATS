<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-asset', function () {
    $service = new \App\Services\AssetApiService();

    return $service
        ->getBag('AST-TAS-001')
        ->json();
});