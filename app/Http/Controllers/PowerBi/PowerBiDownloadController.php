<?php

namespace App\Http\Controllers\PowerBi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PowerBiDownloadController extends Controller
{
    public function connection(): Response
    {
        $payload = [
            'version' => '0.1',
            'connections' => [
                [
                    'details' => [
                        'protocol' => 'odata',
                        'address' => [
                            'url' => route('power_bi.odata.index'),
                        ],
                        'authentication' => null,
                        'query' => null,
                    ],
                    'options' => (object) [],
                    'mode' => null,
                ],
            ],
        ];

        return response(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL)
            ->withHeaders([
                'Content-Type' => 'application/json; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="Dharta-PowerBI.pbids"',
            ]);
    }

    public function template(): BinaryFileResponse
    {
        $templatePath = $this->templatePath();

        abort_unless(is_file($templatePath), 404, 'The Power BI template has not been added yet.');

        return response()->download($templatePath, 'Dharta-PowerBI.pbit');
    }

    public static function templatePath(): string
    {
        return storage_path('app/private/power-bi/Dharta-PowerBI.pbit');
    }
}
