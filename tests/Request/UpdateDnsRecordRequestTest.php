<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Tests\Request;

use Kipchak\Driver\CloudflareDns\Enum\DnsRecordType;
use Kipchak\Driver\CloudflareDns\Request\UpdateDnsRecordRequest;
use PHPUnit\Framework\TestCase;

final class UpdateDnsRecordRequestTest extends TestCase
{
    public function testProxiedIsAlwaysIncluded(): void
    {
        $request = new UpdateDnsRecordRequest(
            type:    DnsRecordType::A,
            name:    'api.example.com',
            content: '192.0.2.1',
        );

        $data = $request->toArray();

        $this->assertArrayHasKey('proxied', $data);
        $this->assertFalse($data['proxied']);
    }

    public function testAllCoreFieldsAreSent(): void
    {
        $request = new UpdateDnsRecordRequest(
            type:    DnsRecordType::AAAA,
            name:    'ipv6.example.com',
            content: '2001:db8::1',
            ttl:     3600,
            proxied: true,
        );

        $data = $request->toArray();

        $this->assertSame('AAAA', $data['type']);
        $this->assertSame('ipv6.example.com', $data['name']);
        $this->assertSame('2001:db8::1', $data['content']);
        $this->assertSame(3600, $data['ttl']);
        $this->assertTrue($data['proxied']);
    }

    public function testOptionalFieldsAreOmittedWhenNotSet(): void
    {
        $request = new UpdateDnsRecordRequest(
            type:    DnsRecordType::A,
            name:    'api.example.com',
            content: '192.0.2.1',
        );

        $data = $request->toArray();

        $this->assertArrayNotHasKey('comment', $data);
        $this->assertArrayNotHasKey('tags', $data);
        $this->assertArrayNotHasKey('priority', $data);
    }

    public function testOptionalFieldsAreIncludedWhenProvided(): void
    {
        $request = new UpdateDnsRecordRequest(
            type:     DnsRecordType::MX,
            name:     'example.com',
            content:  'mail.example.com',
            comment:  'Updated mail server',
            tags:     ['mail'],
            priority: 20,
        );

        $data = $request->toArray();

        $this->assertSame('Updated mail server', $data['comment']);
        $this->assertSame(['mail'], $data['tags']);
        $this->assertSame(20, $data['priority']);
    }
}
