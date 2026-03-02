<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Result;

use Kipchak\Driver\CloudflareDns\Enum\DnsRecordType;

final class DnsRecordResult
{
    /**
     * @param string[] $tags
     */
    public function __construct(
        public readonly string $id,
        public readonly DnsRecordType $type,
        public readonly string $name,
        public readonly string $content,
        public readonly int $ttl,
        public readonly bool $proxied,
        public readonly bool $proxiable,
        public readonly ?string $zoneId,
        public readonly ?string $zoneName,
        public readonly ?string $comment,
        public readonly array $tags,
        public readonly ?int $priority,
        public readonly ?string $createdOn,
        public readonly ?string $modifiedOn,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:         $data['id'],
            type:       DnsRecordType::from($data['type']),
            name:       $data['name'],
            content:    $data['content'],
            ttl:        (int) ($data['ttl'] ?? 1),
            proxied:    (bool) ($data['proxied'] ?? false),
            proxiable:  (bool) ($data['proxiable'] ?? false),
            zoneId:     $data['zone_id'] ?? null,
            zoneName:   $data['zone_name'] ?? null,
            comment:    $data['comment'] ?? null,
            tags:       $data['tags'] ?? [],
            priority:   isset($data['priority']) ? (int) $data['priority'] : null,
            createdOn:  $data['created_on'] ?? null,
            modifiedOn: $data['modified_on'] ?? null,
        );
    }
}
