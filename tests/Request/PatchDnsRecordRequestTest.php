<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Tests\Request;

use Kipchak\Driver\CloudflareDns\Enum\DnsRecordType;
use Kipchak\Driver\CloudflareDns\Request\PatchDnsRecordRequest;
use PHPUnit\Framework\TestCase;

final class PatchDnsRecordRequestTest extends TestCase
{
    public function testEmptyRequestProducesEmptyArray(): void
    {
        $this->assertSame([], (new PatchDnsRecordRequest())->toArray());
    }

    public function testOnlyNonNullFieldsAreIncluded(): void
    {
        $request = new PatchDnsRecordRequest(content: '192.0.2.2');

        $data = $request->toArray();

        $this->assertSame(['content' => '192.0.2.2'], $data);
    }

    public function testAllFieldsIncludedWhenProvided(): void
    {
        $request = new PatchDnsRecordRequest(
            type:     DnsRecordType::A,
            name:     'api.example.com',
            content:  '192.0.2.3',
            ttl:      300,
            proxied:  true,
            comment:  'patched',
            tags:     ['infra'],
            priority: 5,
        );

        $data = $request->toArray();

        $this->assertSame('A', $data['type']);
        $this->assertSame('api.example.com', $data['name']);
        $this->assertSame('192.0.2.3', $data['content']);
        $this->assertSame(300, $data['ttl']);
        $this->assertTrue($data['proxied']);
        $this->assertSame('patched', $data['comment']);
        $this->assertSame(['infra'], $data['tags']);
        $this->assertSame(5, $data['priority']);
    }

    public function testExplicitEmptyTagsArrayIsIncludedToClearTags(): void
    {
        $request = new PatchDnsRecordRequest(tags: []);

        $data = $request->toArray();

        $this->assertArrayHasKey('tags', $data);
        $this->assertSame([], $data['tags']);
    }
}
