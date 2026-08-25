<?php

use App\Http\Controllers\PowerBi\ODataController;
use App\Http\Middleware\AuthenticatePowerBiFeed;
use Illuminate\Support\Facades\Route;

$powerBiMiddleware = [AuthenticatePowerBiFeed::class];

if (! app()->isLocal()) {
    $powerBiMiddleware[] = 'throttle:60,1';
}

Route::prefix('odata')
    ->name('power_bi.odata.')
    ->middleware($powerBiMiddleware)
    ->group(function () {
        Route::get('/', [ODataController::class, 'serviceDocument'])->name('index');
        Route::get('/$metadata', [ODataController::class, 'metadata'])->name('metadata');
        Route::get('/{entitySet}', [ODataController::class, 'entities'])->name('entities');
    });
