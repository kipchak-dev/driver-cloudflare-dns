<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Library;

use Illuminate\Http\Client\Factory;
use InvalidArgumentException;
use Kipchak\Driver\CloudflareDns\Request\CreateDnsRecordRequest;
use Kipchak\Driver\CloudflareDns\Request\ListDnsRecordsRequest;
use Kipchak\Driver\CloudflareDns\Request\ListZonesRequest;
use Kipchak\Driver\CloudflareDns\Request\PatchDnsRecordRequest;
use Kipchak\Driver\CloudflareDns\Request\UpdateDnsRecordRequest;
use Kipchak\Driver\CloudflareDns\Result\DnsRecordResult;
use Kipchak\Driver\CloudflareDns\Result\ZoneResult;
use RuntimeException;

final class CloudflareDnsClient
{
    private readonly string $baseUrl;

    public function __construct(
        private readonly string $apiToken,
        private readonly Factory $http,
        string $baseUrl = 'https://api.cloudflare.com/client/v4',
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * List zones, optionally filtered.
     *
     * @return ZoneResult[]
     */
    public function listZones(ListZonesRequest $request = new ListZonesRequest()): array
    {
        $results = $this->request('GET', '/zones', $request->toArray());

        return array_map(ZoneResult::fromArray(...), $results);
    }

    /**
     * Get details for a single zone.
     */
    public function getZone(string $zoneId): ZoneResult
    {
        return ZoneResult::fromArray($this->request('GET', '/zones/' . $zoneId));
    }

    /**
     * List DNS records for a zone, optionally filtered.
     *
     * @return DnsRecordResult[]
     */
    public function listDnsRecords(string $zoneId, ListDnsRecordsRequest $request = new ListDnsRecordsRequest()): array
    {
        $results = $this->request('GET', '/zones/' . $zoneId . '/dns_records', $request->toArray());

        return array_map(DnsRecordResult::fromArray(...), $results);
    }

    /**
     * Get a single DNS record.
     */
    public function getDnsRecord(string $zoneId, string $recordId): DnsRecordResult
    {
        return DnsRecordResult::fromArray(
            $this->request('GET', '/zones/' . $zoneId . '/dns_records/' . $recordId)
        );
    }

    /**
     * Create a new DNS record in a zone.
     */
    public function createDnsRecord(string $zoneId, CreateDnsRecordRequest $request): DnsRecordResult
    {
        return DnsRecordResult::fromArray(
            $this->request('POST', '/zones/' . $zoneId . '/dns_records', $request->toArray())
        );
    }

    /**
     * Fully replace a DNS record. All fields in UpdateDnsRecordRequest are sent.
     */
    public function updateDnsRecord(string $zoneId, string $recordId, UpdateDnsRecordRequest $request): DnsRecordResult
    {
        return DnsRecordResult::fromArray(
            $this->request('PUT', '/zones/' . $zoneId . '/dns_records/' . $recordId, $request->toArray())
        );
    }

    /**
     * Partially update a DNS record. Only non-null fields in PatchDnsRecordRequest are sent.
     */
    public function patchDnsRecord(string $zoneId, string $recordId, PatchDnsRecordRequest $request): DnsRecordResult
    {
        return DnsRecordResult::fromArray(
            $this->request('PATCH', '/zones/' . $zoneId . '/dns_records/' . $recordId, $request->toArray())
        );
    }

    /**
     * Delete a DNS record.
     *
     * Throws RuntimeException on failure; returns void on success.
     */
    public function deleteDnsRecord(string $zoneId, string $recordId): void
    {
        $this->request('DELETE', '/zones/' . $zoneId . '/dns_records/' . $recordId);
    }

    private function request(string $method, string $path, array $payload = []): array
    {
        $pending = $this->http
            ->withToken($this->apiToken)
            ->asJson()
            ->acceptJson();

        $url = $this->baseUrl . $path;

        $response = match ($method) {
            'GET'    => $pending->get($url, $payload),
            'POST'   => $pending->post($url, $payload),
            'PUT'    => $pending->put($url, $payload),
            'PATCH'  => $pending->patch($url, $payload),
            'DELETE' => $pending->delete($url, $payload),
            default  => throw new InvalidArgumentException('Unsupported HTTP method: ' . $method),
        };

        $body = $response->json();

        if (!($body['success'] ?? false)) {
            $errors  = $body['errors'] ?? [];
            $message = !empty($errors) ? ($errors[0]['message'] ?? 'Unknown error') : 'Cloudflare API request failed';
            $code    = !empty($errors) ? (string) ($errors[0]['code'] ?? 0) : '0';

            throw new RuntimeException(
                '[CF ' . $code . '] ' . $message . ' (HTTP ' . $response->status() . ')'
            );
        }

        return $body['result'] ?? [];
    }
}
