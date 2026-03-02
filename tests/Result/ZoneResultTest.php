<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Tests\Result;

use Kipchak\Driver\CloudflareDns\Enum\ZoneStatus;
use Kipchak\Driver\CloudflareDns\Result\ZoneResult;
use PHPUnit\Framework\TestCase;

final class ZoneResultTest extends TestCase
{
    public function testFromArrayMapsAllFields(): void
    {
        $zone = ZoneResult::fromArray($this->fixture());

        $this->assertSame('023e105f4ecef8ad9ca31a8372d0c353', $zone->id);
        $this->assertSame('example.com', $zone->name);
        $this->assertSame(ZoneStatus::Active, $zone->status);
        $this->assertFalse($zone->paused);
        $this->assertSame('full', $zone->type);
        $this->assertSame(['ns1.cloudflare.com', 'ns2.cloudflare.com'], $zone->nameServers);
        $this->assertSame('acct-123', $zone->accountId);
        $this->assertSame('Test Account', $zone->accountName);
        $this->assertSame('2024-01-01T00:00:00Z', $zone->createdOn);
        $this->assertSame('2024-01-02T00:00:00Z', $zone->modifiedOn);
        $this->assertSame('2024-01-01T01:00:00Z', $zone->activatedOn);
    }

    public function testFromArrayHandlesMissingOptionalFields(): void
    {
        $zone = ZoneResult::fromArray([
            'id'     => 'zone-abc',
            'name'   => 'example.org',
            'status' => 'pending',
            'type'   => 'full',
        ]);

        $this->assertSame(ZoneStatus::Pending, $zone->status);
        $this->assertFalse($zone->paused);
        $this->assertSame([], $zone->nameServers);
        $this->assertNull($zone->accountId);
        $this->assertNull($zone->accountName);
        $this->assertNull($zone->createdOn);
        $this->assertNull($zone->modifiedOn);
        $this->assertNull($zone->activatedOn);
    }

    private function fixture(): array
    {
        return [
            'id'           => '023e105f4ecef8ad9ca31a8372d0c353',
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
}
