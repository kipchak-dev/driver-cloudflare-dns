<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Request;

use Kipchak\Driver\CloudflareDns\Enum\ZoneStatus;

final class ListZonesRequest
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?ZoneStatus $status = null,
        public readonly ?string $accountId = null,
        public readonly ?string $accountName = null,
        public readonly int $page = 1,
        public readonly int $perPage = 20,
        /** @var 'name'|'status'|'account.id'|'account.name'|null */
        public readonly ?string $order = null,
        /** @var 'asc'|'desc'|null */
        public readonly ?string $direction = null,
        /** @var 'all'|'any' */
        public readonly string $match = 'all',
    ) {}

    public function toArray(): array
    {
        $params = [
            'page'     => $this->page,
            'per_page' => $this->perPage,
            'match'    => $this->match,
        ];

        if ($this->name !== null) {
            $params['name'] = $this->name;
        }
        if ($this->status !== null) {
            $params['status'] = $this->status->value;
        }
        if ($this->accountId !== null) {
            $params['account.id'] = $this->accountId;
        }
        if ($this->accountName !== null) {
            $params['account.name'] = $this->accountName;
        }
        if ($this->order !== null) {
            $params['order'] = $this->order;
        }
        if ($this->direction !== null) {
            $params['direction'] = $this->direction;
        }

        return $params;
    }
}
