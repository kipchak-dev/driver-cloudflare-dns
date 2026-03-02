<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Result;

use Kipchak\Driver\CloudflareDns\Enum\ZoneStatus;

final class ZoneResult
{
    /**
     * @param string[]  $nameServers
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ZoneStatus $status,
        public readonly bool $paused,
        public readonly string $type,
        public readonly array $nameServers,
        public readonly ?string $accountId,
        public readonly ?string $accountName,
        public readonly ?string $createdOn,
        public readonly ?string $modifiedOn,
        public readonly ?string $activatedOn,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:          $data['id'],
            name:        $data['name'],
            status:      ZoneStatus::from($data['status']),
            paused:      (bool) ($data['paused'] ?? false),
            type:        $data['type'] ?? 'full',
            nameServers: $data['name_servers'] ?? [],
            accountId:   $data['account']['id'] ?? null,
            accountName: $data['account']['name'] ?? null,
            createdOn:   $data['created_on'] ?? null,
            modifiedOn:  $data['modified_on'] ?? null,
            activatedOn: $data['activated_on'] ?? null,
        );
    }
}
