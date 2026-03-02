<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Tests\Library;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Kipchak\Driver\CloudflareDns\Enum\DnsRecordType;
use Kipchak\Driver\CloudflareDns\Enum\ZoneStatus;
use Kipchak\Driver\CloudflareDns\Library\CloudflareDnsClient;
use Kipchak\Driver\CloudflareDns\Request\CreateDnsRecordRequest;
use Kipchak\Driver\CloudflareDns\Request\ListDnsRecordsRequest;
use Kipchak\Driver\CloudflareDns\Request\ListZonesRequest;
use Kipchak\Driver\CloudflareDns\Request\PatchDnsRecordRequest;
use Kipchak\Driver\CloudflareDns\Request\UpdateDnsRecordRequest;
use Kipchak\Driver\CloudflareDns\Result\DnsRecordResult;
use Kipchak\Driver\CloudflareDns\Result\ZoneResult;
use PHPUnit\Framework\TestCase;

final class CloudflareDnsClientTest extends TestCase
{
    private const TOKEN   = 'test-api-token';
    private const ZONE_ID = '023e105f4ecef8ad9ca31a8372d0c353';
    private const REC_ID  = 'dns-record-123';

    // -------------------------------------------------------------------------
    // Zones
    // -------------------------------------------------------------------------

    public function testListZonesReturnsZoneResultArray(): void
    {
        $http = $this->fakeHttp([
            'https://api.cloudflare.com/client/v4/zones*' => Factory::response($this->zoneListResponse(), 200),
        ]);

        $results = $this->client($http)->listZones();

        $this->assertCount(1, $results);
        $this->assertInstanceOf(ZoneResult::class, $results[0]);
        $this->assertSame(self::ZONE_ID, $results[0]->id);
        $this->assertSame(ZoneStatus::Active, $results[0]->status);
    }

    public function testListZonesForwardsFilterParams(): void
    {
        $http = $this->fakeHttp([
            'https://api.cloudflare.com/client/v4/zones*' => Factory::response($this->zoneListResponse(), 200),
        ]);

        $this->client($http)->listZones(new ListZonesRequest(
            name:   'example.com',
            status: ZoneStatus::Active,
        ));

        $http->assertSent(function (Request $request) {
            return str_contains($request->url(), 'name=example.com')
                && str_contains($request->url(), 'status=active');
        });
    }

    public function testGetZoneReturnsZoneResult(): void
    {
        $http = $this->fakeHttp([
            'https://api.cloudflare.com/client/v4/zones/' . self::ZONE_ID => Factory::response(
                $this->singleResult($this->zoneFixture()), 200
            ),
        ]);

        $zone = $this->client($http)->getZone(self::ZONE_ID);

        $this->assertInstanceOf(ZoneResult::class, $zone);
        $this->assertSame(self::ZONE_ID, $zone->id);
        $this->assertSame('example.com', $zone->name);
    }

    // -------------------------------------------------------------------------
    // DNS Records
    // -------------------------------------------------------------------------

    public function testListDnsRecordsReturnsDnsRecordResultArray(): void
    {
        $http = $this->fakeHttp([
            'https://api.cloudflare.com/client/v4/zones/' . self::ZONE_ID . '/dns_records*' => Factory::response(
                $this->dnsListResponse(), 200
            ),
        ]);

        $results = $this->client($http)->listDnsRecords(self::ZONE_ID);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(DnsRecordResult::class, $results[0]);
        $this->assertSame(self::REC_ID, $results[0]->id);
    }

    public function testListDnsRecordsForwardsFilterParams(): void
    {
        $http = $this->fakeHttp([
            'https://api.cloudflare.com/client/v4/zones/' . self::ZONE_ID . '/dns_records*' => Factory::response(
                $this->dnsListResponse(), 200
            ),
        ]);

        $this->client($http)->listDnsRecords(self::ZONE_ID, new ListDnsRecordsRequest(
            type: DnsRecordType::A,
            name: 'api.example.com',
        ));

        $http->assertSent(function (Request $request) {
            return str_contains($request->url(), 'type=A')
                && str_contains($request->url(), 'name=api.example.com');
        });
    }

    public function testGetDnsRecordReturnsDnsRecordResult(): void
    {
        $http = $this->fakeHttp([
            'https://api.cloudflare.com/client/v4/zones/' . self::ZONE_ID . '/dns_records/' . self::REC_ID => Factory::response(
                $this->singleResult($this->dnsRecordFixture()), 200
            ),
        ]);

        $record = $this->client($http)->getDnsRecord(self::ZONE_ID, self::REC_ID);

        $this->assertInstanceOf(DnsRecordResult::class, $record);
        $this->assertSame(self::REC_ID, $record->id);
        $this->assertSame(DnsRecordType::A, $record->type);
    }

    public function testCreateDnsRecordSendsPostAndReturnsResult(): void
    {
        $http = $this->fakeHttp([
            'https://api.cloudflare.com/client/v4/zones/' . self::ZONE_ID . '/dns_records' => Factory::response(
                $this->singleResult($this->dnsRecordFixture()), 201
            ),
        ]);

        $record = $this->client($http)->createDnsRecord(self::ZONE_ID, new CreateDnsRecordRequest(
            type:    DnsRecordType::A,
            name:    'api.example.com',
            content: '192.0.2.1',
        ));

        $this->assertInstanceOf(DnsRecordResult::class, $record);

        $http->assertSent(function (Request $request) {
            return $request->method() === 'POST'
                && $request->data()['type'] === 'A'
                && $request->data()['name'] === 'api.example.com'
                && $request->data()['content'] === '192.0.2.1';
        });
    }

    public function testUpdateDnsRecordSendsPutWithAllFields(): void
    {
        $http = $this->fakeHttp([
            'https://api.cloudflare.com/client/v4/zones/' . self::ZONE_ID . '/dns_records/' . self::REC_ID => Factory::response(
                $this->singleResult($this->dnsRecordFixture()), 200
            ),
        ]);

        $this->client($http)->updateDnsRecord(self::ZONE_ID, self::REC_ID, new UpdateDnsRecordRequest(
            type:    DnsRecordType::A,
            name:    'api.example.com',
            content: '192.0.2.2',
            proxied: false,
        ));

        $http->assertSent(function (Request $request) {
            $data = $request->data();
            return $request->method() === 'PUT'
                && array_key_exists('proxied', $data)
                && $data['content'] === '192.0.2.2';
        });
    }

    public function testPatchDnsRecordSendsPatchWithOnlyNonNullFields(): void
    {
        $http = $this->fakeHttp([
            'https://api.cloudflare.com/client/v4/zones/' . self::ZONE_ID . '/dns_records/' . self::REC_ID => Factory::response(
                $this->singleResult($this->dnsRecordFixture()), 200
            ),
        ]);

        $this->client($http)->patchDnsRecord(self::ZONE_ID, self::REC_ID, new PatchDnsRecordRequest(
            content: '192.0.2.3',
        ));

        $http->assertSent(function (Request $request) {
            $data = $request->data();
            return $request->method() === 'PATCH'
                && $data === ['content' => '192.0.2.3'];
        });
    }

    public function testDeleteDnsRecordSendsDeleteRequest(): void
    {
        $http = $this->fakeHttp([
            'https://api.cloudflare.com/client/v4/zones/' . self::ZONE_ID . '/dns_records/' . self::REC_ID => Factory::response(
                ['success' => true, 'result' => ['id' => self::REC_ID], 'errors' => [], 'messages' => []], 200
            ),
        ]);

        $this->client($http)->deleteDnsRecord(self::ZONE_ID, self::REC_ID);

        $http->assertSent(function (Request $request) {
            return $request->method() === 'DELETE';
        });
    }

    // -------------------------------------------------------------------------
    // Auth header
    // -------------------------------------------------------------------------

    public function testBearerTokenIsSentWithEveryRequest(): void
    {
        $http = $this->fakeHttp([
            'https://api.cloudflare.com/client/v4/zones*' => Factory::response($this->zoneListResponse(), 200),
        ]);

        $this->client($http)->listZones();

        $http->assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer ' . self::TOKEN);
        });
    }

    // -------------------------------------------------------------------------
    // Error handling
    // -------------------------------------------------------------------------

    public function testApiErrorThrowsRuntimeException(): void
    {
        $http = $this->fakeHttp([
            'https://api.cloudflare.com/client/v4/zones*' => Factory::response([
                'success'  => false,
                'errors'   => [['code' => 7003, 'message' => 'Could not route to /zones.']],
                'messages' => [],
                'result'   => null,
            ], 400),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/\[CF 7003\]/');

        $this->client($http)->listZones();
    }

    public function testHttpErrorWithNoBodyThrowsRuntimeException(): void
    {
        $http = $this->fakeHttp([
            'https://api.cloudflare.com/client/v4/zones*' => Factory::response([
                'success' => false,
                'errors'  => [],
                'result'  => null,
            ], 500),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Cloudflare API request failed/');

        $this->client($http)->listZones();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function client(Factory $http): CloudflareDnsClient
    {
        return new CloudflareDnsClient(self::TOKEN, $http);
    }

    private function fakeHttp(array $responses): Factory
    {
        $http = new Factory();
        $http->fake($responses);
        return $http;
    }

    private function singleResult(array $result): array
    {
        return ['success' => true, 'result' => $result, 'errors' => [], 'messages' => []];
    }

    private function zoneListResponse(): array
    {
        return ['success' => true, 'result' => [$this->zoneFixture()], 'errors' => [], 'messages' => []];
    }

    private function dnsListResponse(): array
    {
        return ['success' => true, 'result' => [$this->dnsRecordFixture()], 'errors' => [], 'messages' => []];
    }

    private function zoneFixture(): array
    {
        return [
            'id'           => self::ZONE_ID,
            'name'         => 'example.com',
            'status'       => 'active',
            'paused'       => false,
            'type'         => 'full',
            'name_servers' => ['ns1.cloudflare.com', 'ns2.cloudflare.com'],
            'account'      => ['id' => 'acct-123', 'name' => 'Test Account'],
            'created_on'   => '2024-01-01T00:00:00Z',
            'modified_on'  => '2024-01-02T00:00:00Z',
            'activated_on' => '2024-01-01T01:00:00Z',
        ];
    }

    private function dnsRecordFixture(): array
    {
        return [
            'id'          => self::REC_ID,
            'type'        => 'A',
            'name'        => 'api.example.com',
            'content'     => '192.0.2.1',
            'ttl'         => 1,
            'proxied'     => false,
            'proxiable'   => true,
            'zone_id'     => self::ZONE_ID,
            'zone_name'   => 'example.com',
            'comment'     => 'API server',
            'tags'        => [],
            'created_on'  => '2024-01-01T00:00:00Z',
            'modified_on' => '2024-01-02T00:00:00Z',
        ];
    }
}
