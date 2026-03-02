<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Tests\Request;

use Kipchak\Driver\CloudflareDns\Enum\ZoneStatus;
use Kipchak\Driver\CloudflareDns\Request\ListZonesRequest;
use PHPUnit\Framework\TestCase;

final class ListZonesRequestTest extends TestCase
{
    public function testDefaultsAreIncluded(): void
    {
        $data = (new ListZonesRequest())->toArray();

        $this->assertSame(1, $data['page']);
        $this->assertSame(20, $data['per_page']);
        $this->assertSame('all', $data['match']);
    }

    public function testNullFieldsAreOmitted(): void
    {
        $data = (new ListZonesRequest())->toArray();

        $this->assertArrayNotHasKey('name', $data);
        $this->assertArrayNotHasKey('status', $data);
        $this->assertArrayNotHasKey('account.id', $data);
        $this->assertArrayNotHasKey('account.name', $data);
        $this->assertArrayNotHasKey('order', $data);
        $this->assertArrayNotHasKey('direction', $data);
    }

    public function testStatusEnumIsSerialised(): void
    {
        $data = (new ListZonesRequest(status: ZoneStatus::Active))->toArray();

        $this->assertSame('active', $data['status']);
    }

    public function testAccountFieldsUseDotNotation(): void
    {
        $data = (new ListZonesRequest(
            accountId:   'acct-123',
            accountName: 'Test Account',
        ))->toArray();

        $this->assertSame('acct-123', $data['account.id']);
        $this->assertSame('Test Account', $data['account.name']);
        $this->assertArrayNotHasKey('accountId', $data);
        $this->assertArrayNotHasKey('accountName', $data);
    }

    public function testPaginationAndOrderingAreIncluded(): void
    {
        $data = (new ListZonesRequest(
            page:      3,
            perPage:   50,
            order:     'name',
            direction: 'desc',
            match:     'any',
        ))->toArray();

        $this->assertSame(3, $data['page']);
        $this->assertSame(50, $data['per_page']);
        $this->assertSame('name', $data['order']);
        $this->assertSame('desc', $data['direction']);
        $this->assertSame('any', $data['match']);
    }
}
