<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Tests\Request;

use Kipchak\Driver\CloudflareDns\Enum\DnsRecordType;
use Kipchak\Driver\CloudflareDns\Request\ListDnsRecordsRequest;
use PHPUnit\Framework\TestCase;

final class ListDnsRecordsRequestTest extends TestCase
{
    public function testDefaultsAreIncluded(): void
    {
        $data = (new ListDnsRecordsRequest())->toArray();

        $this->assertSame(1, $data['page']);
        $this->assertSame(100, $data['per_page']);
        $this->assertSame('all', $data['match']);
    }

    public function testNullFieldsAreOmitted(): void
    {
        $data = (new ListDnsRecordsRequest())->toArray();

        $this->assertArrayNotHasKey('type', $data);
        $this->assertArrayNotHasKey('name', $data);
        $this->assertArrayNotHasKey('content', $data);
        $this->assertArrayNotHasKey('proxied', $data);
        $this->assertArrayNotHasKey('comment', $data);
        $this->assertArrayNotHasKey('search', $data);
        $this->assertArrayNotHasKey('order', $data);
        $this->assertArrayNotHasKey('direction', $data);
    }

    public function testRecordTypeEnumIsSerialised(): void
    {
        $data = (new ListDnsRecordsRequest(type: DnsRecordType::MX))->toArray();

        $this->assertSame('MX', $data['type']);
    }

    public function testAllFiltersAreIncludedWhenSet(): void
    {
        $data = (new ListDnsRecordsRequest(
            type:      DnsRecordType::A,
            name:      'api.example.com',
            content:   '192.0.2.1',
            proxied:   false,
            comment:   'test',
            search:    'api',
            match:     'any',
            order:     'name',
            direction: 'asc',
            page:      2,
            perPage:   50,
        ))->toArray();

        $this->assertSame('A', $data['type']);
        $this->assertSame('api.example.com', $data['name']);
        $this->assertSame('192.0.2.1', $data['content']);
        $this->assertFalse($data['proxied']);
        $this->assertSame('test', $data['comment']);
        $this->assertSame('api', $data['search']);
        $this->assertSame('any', $data['match']);
        $this->assertSame('name', $data['order']);
        $this->assertSame('asc', $data['direction']);
        $this->assertSame(2, $data['page']);
        $this->assertSame(50, $data['per_page']);
    }
}
