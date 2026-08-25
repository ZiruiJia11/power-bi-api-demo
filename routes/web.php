<?php

use App\Http\Controllers\PowerBi\PowerBiDownloadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('data-report/power-bi-connection.pbids', [PowerBiDownloadController::class, 'connection'])
        ->name('power_bi.download.connection');
    Route::get('data-report/dharta-power-bi.pbit', [PowerBiDownloadController::class, 'template'])
        ->name('power_bi.download.template');
    Route::get('data-report', fn () => Inertia::render('DataReport', [
        'odataUrl' => route('power_bi.odata.index'),
        'demoCredentials' => app()->isProduction()
            ? []
            : collect(config('services.power_bi.tenants'))->map(fn (array $tenant, string $key): array => [
                'tenant' => $key,
                'name' => $tenant['name'],
                'username' => $tenant['username'],
                'password' => $tenant['token'],
            ])->values(),
        'datasets' => ['Projects', 'Nodes', 'Dependencies', 'Users'],
        'connectionDownloadUrl' => route('power_bi.download.connection'),
        'templateDownloadUrl' => route('power_bi.download.template'),
        'templateAvailable' => is_file(PowerBiDownloadController::templatePath()),
    ]))->name('data_report');
});

require __DIR__.'/settings.php';
