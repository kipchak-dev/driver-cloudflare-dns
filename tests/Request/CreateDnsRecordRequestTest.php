<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Tests\Request;

use Kipchak\Driver\CloudflareDns\Enum\DnsRecordType;
use Kipchak\Driver\CloudflareDns\Request\CreateDnsRecordRequest;
use PHPUnit\Framework\TestCase;

final class CreateDnsRecordRequestTest extends TestCase
{
    public function testRequiredFieldsAreAlwaysPresent(): void
    {
        $request = new CreateDnsRecordRequest(
            type:    DnsRecordType::A,
            name:    'api.example.com',
            content: '192.0.2.1',
        );

        $data = $request->toArray();

        $this->assertSame('A', $data['type']);
        $this->assertSame('api.example.com', $data['name']);
        $this->assertSame('192.0.2.1', $data['content']);
        $this->assertSame(1, $data['ttl']);
    }

    public function testOptionalFieldsAreOmittedWhenNotSet(): void
    {
        $request = new CreateDnsRecordRequest(
            type:    DnsRecordType::TXT,
            name:    'example.com',
            content: 'v=spf1 include:example.com ~all',
        );

        $data = $request->toArray();

        $this->assertArrayNotHasKey('proxied', $data);
        $this->assertArrayNotHasKey('comment', $data);
        $this->assertArrayNotHasKey('tags', $data);
        $this->assertArrayNotHasKey('priority', $data);
    }

    public function testOptionalFieldsAreIncludedWhenProvided(): void
    {
        $request = new CreateDnsRecordRequest(
            type:     DnsRecordType::MX,
            name:     'example.com',
            content:  'mail.example.com',
            ttl:      3600,
            proxied:  false,
            comment:  'Primary mail server',
            tags:     ['mail', 'primary'],
            priority: 10,
        );

        $data = $request->toArray();

        $this->assertSame(3600, $data['ttl']);
        $this->assertFalse($data['proxied']);
        $this->assertSame('Primary mail server', $data['comment']);
        $this->assertSame(['mail', 'primary'], $data['tags']);
        $this->assertSame(10, $data['priority']);
    }

    public function testEmptyTagsArrayIsOmitted(): void
    {
        $request = new CreateDnsRecordRequest(
            type:    DnsRecordType::A,
            name:    'api.example.com',
            content: '192.0.2.1',
            tags:    [],
        );

        $this->assertArrayNotHasKey('tags', $request->toArray());
    }
}
