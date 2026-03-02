<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Request;

use Kipchak\Driver\CloudflareDns\Enum\DnsRecordType;

final class ListDnsRecordsRequest
{
    public function __construct(
        public readonly ?DnsRecordType $type = null,
        public readonly ?string $name = null,
        public readonly ?string $content = null,
        public readonly ?bool $proxied = null,
        public readonly ?string $comment = null,
        /** Human-readable search across name, content and comment */
        public readonly ?string $search = null,
        /** @var 'all'|'any' */
        public readonly string $match = 'all',
        /** @var 'type'|'name'|'content'|'ttl'|'proxied'|null */
        public readonly ?string $order = null,
        /** @var 'asc'|'desc'|null */
        public readonly ?string $direction = null,
        public readonly int $page = 1,
        public readonly int $perPage = 100,
    ) {}

    public function toArray(): array
    {
        $params = [
            'page'     => $this->page,
            'per_page' => $this->perPage,
            'match'    => $this->match,
        ];

        if ($this->type !== null) {
            $params['type'] = $this->type->value;
        }
        if ($this->name !== null) {
            $params['name'] = $this->name;
        }
        if ($this->content !== null) {
            $params['content'] = $this->content;
        }
        if ($this->proxied !== null) {
            $params['proxied'] = $this->proxied;
        }
        if ($this->comment !== null) {
            $params['comment'] = $this->comment;
        }
        if ($this->search !== null) {
            $params['search'] = $this->search;
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
