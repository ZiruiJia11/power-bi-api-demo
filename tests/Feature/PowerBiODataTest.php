<?php

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    config()->set('services.power_bi.tenants', [
        'acme' => [
            'name' => 'Acme Construction',
            'username' => 'acme',
            'token' => 'acme-test-token',
        ],
        'buildco' => [
            'name' => 'BuildCo',
            'username' => 'buildco',
            'token' => 'buildco-test-token',
        ],
    ]);
});

test('the OData feed requires a valid integration credential', function () {
    $this->getJson(route('power_bi.odata.index'))
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Basic realm="Dharta Power BI", charset="UTF-8"')
        ->assertJsonPath('error.code', 'Unauthorized');
});

test('invalid Basic credentials are rejected', function () {
    $this->withBasicAuth('acme', 'wrong-token')
        ->getJson(route('power_bi.odata.index'))
        ->assertUnauthorized();
});

test('non-local OData routes remain rate limited', function () {
    expect(Route::getRoutes()->getByName('power_bi.odata.index')?->gatherMiddleware())
        ->toContain('throttle:60,1');
});

test('Power BI can discover entity sets with Basic authentication', function () {
    $this->withBasicAuth('acme', 'acme-test-token')
        ->getJson(route('power_bi.odata.index'))
        ->assertSuccessful()
        ->assertHeader('OData-Version', '4.0')
        ->assertJsonFragment(['@odata.context' => url('/api/odata/$metadata')])
        ->assertJsonPath('value.0.name', 'Projects')
        ->assertJsonCount(4, 'value');
});

test('each credential only receives its own tenant data', function () {
    $this->withBasicAuth('acme', 'acme-test-token')
        ->getJson(route('power_bi.odata.entities', ['entitySet' => 'Projects']))
        ->assertSuccessful()
        ->assertJsonPath('value.0.Name', 'City Rail Upgrade')
        ->assertJsonMissing(['Name' => 'BuildCo Civic Centre']);

    $this->withBasicAuth('buildco', 'buildco-test-token')
        ->getJson(route('power_bi.odata.entities', ['entitySet' => 'Projects']))
        ->assertSuccessful()
        ->assertJsonPath('value.0.Name', 'BuildCo Civic Centre')
        ->assertJsonMissing(['Name' => 'City Rail Upgrade']);
});

test('Bearer tokens remain supported for programmatic clients', function () {
    $this->withToken('acme-test-token')
        ->getJson(route('power_bi.odata.entities', ['entitySet' => 'Projects']))
        ->assertSuccessful()
        ->assertJsonPath('value.0.Name', 'City Rail Upgrade');
});

test('Power BI can read OData metadata with Basic authentication', function () {
    $this->withBasicAuth('acme', 'acme-test-token')
        ->get(route('power_bi.odata.metadata'))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/xml;charset=utf-8')
        ->assertHeader('OData-Version', '4.0')
        ->assertSee('EntitySet Name="Projects"', false)
        ->assertSee('EntitySet Name="Dependencies"', false);
});

test('unknown entity sets return not found', function () {
    $this->withBasicAuth('acme', 'acme-test-token')
        ->getJson(route('power_bi.odata.entities', ['entitySet' => 'Unknown']))
        ->assertNotFound();
});
