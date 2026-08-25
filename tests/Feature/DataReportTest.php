<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('data_report'));

    $response->assertRedirect(route('login'));
});

test('authenticated verified users can visit the data report', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('data_report'));

    $response->assertSuccessful();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('DataReport')
        ->where('odataUrl', route('power_bi.odata.index'))
        ->where('demoCredentials', [
            [
                'tenant' => 'acme',
                'name' => 'Acme Construction',
                'username' => config('services.power_bi.tenants.acme.username'),
                'password' => config('services.power_bi.tenants.acme.token'),
            ],
            [
                'tenant' => 'buildco',
                'name' => 'BuildCo',
                'username' => config('services.power_bi.tenants.buildco.username'),
                'password' => config('services.power_bi.tenants.buildco.token'),
            ],
        ])
        ->where('datasets', ['Projects', 'Nodes', 'Dependencies', 'Users'])
        ->where('connectionDownloadUrl', route('power_bi.download.connection'))
        ->where('templateDownloadUrl', route('power_bi.download.template'))
        ->where('templateAvailable', false));
});
