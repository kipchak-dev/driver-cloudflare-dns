<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Tests\Result;

use Kipchak\Driver\CloudflareDns\Enum\DnsRecordType;
use Kipchak\Driver\CloudflareDns\Result\DnsRecordResult;
use PHPUnit\Framework\TestCase;

final class DnsRecordResultTest extends TestCase
{
    public function testFromArrayMapsAllFields(): void
    {
        $record = DnsRecordResult::fromArray($this->fixture());

        $this->assertSame('dns-record-123', $record->id);
        $this->assertSame(DnsRecordType::A, $record->type);
        $this->assertSame('api.example.com', $record->name);
        $this->assertSame('192.0.2.1', $record->content);
        $this->assertSame(1, $record->ttl);
        $this->assertFalse($record->proxied);
        $this->assertTrue($record->proxiable);
        $this->assertSame('023e105f4ecef8ad9ca31a8372d0c353', $record->zoneId);
        $this->assertSame('example.com', $record->zoneName);
        $this->assertSame('API server', $record->comment);
        $this->assertSame([], $record->tags);
        $this->assertNull($record->priority);
        $this->assertSame('2024-01-01T00:00:00Z', $record->createdOn);
        $this->assertSame('2024-01-02T00:00:00Z', $record->modifiedOn);
    }

    public function testFromArrayHandlesMissingOptionalFields(): void
    {
        $record = DnsRecordResult::fromArray([
            'id'      => 'dns-minimal',
            'type'    => 'TXT',
            'name'    => 'example.com',
            'content' => 'v=spf1 ~all',
        ]);

        $this->assertSame(DnsRecordType::TXT, $record->type);
        $this->assertSame(1, $record->ttl);
        $this->assertFalse($record->proxied);
        $this->assertFalse($record->proxiable);
        $this->assertNull($record->zoneId);
        $this->assertNull($record->zoneName);
        $this->assertNull($record->comment);
        $this->assertSame([], $record->tags);
        $this->assertNull($record->priority);
        $this->assertNull($record->createdOn);
        $this->assertNull($record->modifiedOn);
    }

    public function testFromArrayMapsPriority(): void
    {
        $record = DnsRecordResult::fromArray(array_merge($this->fixture(), [
            'type'     => 'MX',
            'content'  => 'mail.example.com',
            'priority' => 10,
        ]));

        $this->assertSame(DnsRecordType::MX, $record->type);
        $this->assertSame(10, $record->priority);
    }

    private function fixture(): array
    {
        return [
            'id'          => 'dns-record-123',
            'type'        => 'A',
            'name'        => 'api.example.com',
            'content'     => '192.0.2.1',
            'ttl'         => 1,
            'proxied'     => false,
            'proxiable'   => true,
            'zone_id'     => '023e105f4ecef8ad9ca31a8372d0c353',
            'zone_name'   => 'example.com',
            'comment'     => 'API server',
            'tags'        => [],
            'created_on'  => '2024-01-01T00:00:00Z',
            'modified_on' => '2024-01-02T00:00:00Z',
        ];
    }
}
