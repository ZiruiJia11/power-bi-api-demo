<?php

use App\Models\User;

test('guests cannot download Power BI integration files', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with([
    'connection file' => 'power_bi.download.connection',
    'report template' => 'power_bi.download.template',
]);

test('verified users can download a Power BI data source file', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('power_bi.download.connection'))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/json; charset=utf-8')
        ->assertHeader('Content-Disposition', 'attachment; filename="Dharta-PowerBI.pbids"')
        ->assertJsonPath('connections.0.details.protocol', 'odata')
        ->assertJsonPath('connections.0.details.address.url', route('power_bi.odata.index'));

    expect($response->getContent())->toContain('"options": {}');
});

test('the report template download is unavailable until a real template is added', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('power_bi.download.template'))
        ->assertNotFound();
});
