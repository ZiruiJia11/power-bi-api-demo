<?php

namespace App\Http\Controllers\PowerBi;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthenticatePowerBiFeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ODataController extends Controller
{
    public function serviceDocument(): JsonResponse
    {
        return response()->json([
            '@odata.context' => $this->metadataUrl(),
            'value' => collect($this->entitySets())->map(fn (string $name): array => [
                'name' => $name,
                'kind' => 'EntitySet',
                'url' => $name,
            ])->values(),
        ])->withHeaders($this->odataHeaders('application/json;odata.metadata=minimal;charset=utf-8'));
    }

    public function metadata(): Response
    {
        return response($this->metadataXml(), 200, $this->odataHeaders('application/xml;charset=utf-8'));
    }

    public function entities(Request $request, string $entitySet): JsonResponse
    {
        $tenant = $request->attributes->get(AuthenticatePowerBiFeed::TENANT_ATTRIBUTE);

        abort_unless(is_string($tenant), 401);

        $datasets = $this->datasets($tenant);

        abort_unless(array_key_exists($entitySet, $datasets), 404);

        return response()->json([
            '@odata.context' => $this->metadataUrl().'#'.$entitySet,
            'value' => $datasets[$entitySet],
        ])->withHeaders($this->odataHeaders('application/json;odata.metadata=minimal;charset=utf-8'));
    }

    /**
     * @return array<string, list<array<string, bool|int|string|null>>>
     */
    private function datasets(string $tenant): array
    {
        $tenantDatasets = [
            'acme' => [
                'Projects' => [
                    ['Id' => 101, 'Name' => 'City Rail Upgrade', 'Status' => 'Active', 'Owner' => 'Aroha Ngata', 'StartDate' => '2026-01-12', 'UpdatedAt' => '2026-08-18T00:42:00Z'],
                    ['Id' => 102, 'Name' => 'Harbour Precinct', 'Status' => 'Planning', 'Owner' => 'James Wilson', 'StartDate' => '2026-05-04', 'UpdatedAt' => '2026-08-17T23:15:00Z'],
                    ['Id' => 103, 'Name' => 'Northern Hospital', 'Status' => 'At Risk', 'Owner' => 'Mia Chen', 'StartDate' => '2025-11-20', 'UpdatedAt' => '2026-08-17T21:08:00Z'],
                ],
                'Nodes' => [
                    ['Id' => 1001, 'ProjectId' => 101, 'Name' => 'Central Station', 'Type' => 'Site', 'Progress' => 68, 'UpdatedAt' => '2026-08-18T00:40:00Z'],
                    ['Id' => 1002, 'ProjectId' => 101, 'Name' => 'Signal Package', 'Type' => 'Work Package', 'Progress' => 42, 'UpdatedAt' => '2026-08-17T22:30:00Z'],
                    ['Id' => 1003, 'ProjectId' => 102, 'Name' => 'Public Realm', 'Type' => 'Design', 'Progress' => 27, 'UpdatedAt' => '2026-08-17T20:10:00Z'],
                ],
                'Dependencies' => [
                    ['Id' => 5001, 'ProjectId' => 101, 'FromNodeId' => 1001, 'ToNodeId' => 1002, 'Status' => 'Open', 'IsCritical' => true],
                    ['Id' => 5002, 'ProjectId' => 102, 'FromNodeId' => 1003, 'ToNodeId' => null, 'Status' => 'Resolved', 'IsCritical' => false],
                ],
                'Users' => [
                    ['Id' => 1, 'Name' => 'Aroha Ngata', 'Role' => 'Project Manager', 'Active' => true],
                    ['Id' => 2, 'Name' => 'James Wilson', 'Role' => 'Planner', 'Active' => true],
                    ['Id' => 3, 'Name' => 'Mia Chen', 'Role' => 'Administrator', 'Active' => true],
                ],
            ],
            'buildco' => [
                'Projects' => [
                    ['Id' => 201, 'Name' => 'BuildCo Civic Centre', 'Status' => 'Active', 'Owner' => 'Olivia Brown', 'StartDate' => '2026-02-16', 'UpdatedAt' => '2026-08-19T02:10:00Z'],
                    ['Id' => 202, 'Name' => 'BuildCo Logistics Hub', 'Status' => 'Planning', 'Owner' => 'Noah Taylor', 'StartDate' => '2026-07-01', 'UpdatedAt' => '2026-08-19T01:20:00Z'],
                ],
                'Nodes' => [
                    ['Id' => 2001, 'ProjectId' => 201, 'Name' => 'Foundation Package', 'Type' => 'Work Package', 'Progress' => 54, 'UpdatedAt' => '2026-08-19T02:00:00Z'],
                    ['Id' => 2002, 'ProjectId' => 202, 'Name' => 'Concept Design', 'Type' => 'Design', 'Progress' => 18, 'UpdatedAt' => '2026-08-19T01:00:00Z'],
                ],
                'Dependencies' => [
                    ['Id' => 6001, 'ProjectId' => 201, 'FromNodeId' => 2001, 'ToNodeId' => null, 'Status' => 'Open', 'IsCritical' => false],
                ],
                'Users' => [
                    ['Id' => 11, 'Name' => 'Olivia Brown', 'Role' => 'Project Director', 'Active' => true],
                    ['Id' => 12, 'Name' => 'Noah Taylor', 'Role' => 'Planner', 'Active' => true],
                ],
            ],
        ];

        return $tenantDatasets[$tenant] ?? [
            'Projects' => [],
            'Nodes' => [],
            'Dependencies' => [],
            'Users' => [],
        ];
    }

    /**
     * @return list<string>
     */
    private function entitySets(): array
    {
        return [
            'Projects',
            'Nodes',
            'Dependencies',
            'Users',
        ];
    }

    private function metadataXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<edmx:Edmx Version="4.0" xmlns:edmx="http://docs.oasis-open.org/odata/ns/edmx">
  <edmx:DataServices>
    <Schema Namespace="Dharta.PowerBi" xmlns="http://docs.oasis-open.org/odata/ns/edm">
      <EntityType Name="Project"><Key><PropertyRef Name="Id"/></Key><Property Name="Id" Type="Edm.Int32" Nullable="false"/><Property Name="Name" Type="Edm.String"/><Property Name="Status" Type="Edm.String"/><Property Name="Owner" Type="Edm.String"/><Property Name="StartDate" Type="Edm.Date"/><Property Name="UpdatedAt" Type="Edm.DateTimeOffset"/></EntityType>
      <EntityType Name="Node"><Key><PropertyRef Name="Id"/></Key><Property Name="Id" Type="Edm.Int32" Nullable="false"/><Property Name="ProjectId" Type="Edm.Int32"/><Property Name="Name" Type="Edm.String"/><Property Name="Type" Type="Edm.String"/><Property Name="Progress" Type="Edm.Int32"/><Property Name="UpdatedAt" Type="Edm.DateTimeOffset"/></EntityType>
      <EntityType Name="Dependency"><Key><PropertyRef Name="Id"/></Key><Property Name="Id" Type="Edm.Int32" Nullable="false"/><Property Name="ProjectId" Type="Edm.Int32"/><Property Name="FromNodeId" Type="Edm.Int32"/><Property Name="ToNodeId" Type="Edm.Int32"/><Property Name="Status" Type="Edm.String"/><Property Name="IsCritical" Type="Edm.Boolean"/></EntityType>
      <EntityType Name="User"><Key><PropertyRef Name="Id"/></Key><Property Name="Id" Type="Edm.Int32" Nullable="false"/><Property Name="Name" Type="Edm.String"/><Property Name="Role" Type="Edm.String"/><Property Name="Active" Type="Edm.Boolean"/></EntityType>
      <EntityContainer Name="Container"><EntitySet Name="Projects" EntityType="Dharta.PowerBi.Project"/><EntitySet Name="Nodes" EntityType="Dharta.PowerBi.Node"/><EntitySet Name="Dependencies" EntityType="Dharta.PowerBi.Dependency"/><EntitySet Name="Users" EntityType="Dharta.PowerBi.User"/></EntityContainer>
    </Schema>
  </edmx:DataServices>
</edmx:Edmx>
XML;
    }

    private function metadataUrl(): string
    {
        return rtrim(url('/api/odata'), '/').'/$metadata';
    }

    /**
     * @return array<string, string>
     */
    private function odataHeaders(string $contentType): array
    {
        return [
            'Content-Type' => $contentType,
            'OData-Version' => '4.0',
            'DataServiceVersion' => '4.0',
        ];
    }
}
