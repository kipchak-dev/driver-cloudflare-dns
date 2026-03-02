<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Request;

use Kipchak\Driver\CloudflareDns\Enum\DnsRecordType;

/**
 * Partial update of an existing DNS record (PATCH).
 * Only non-null fields are sent to Cloudflare; omitted fields are left unchanged.
 */
final class PatchDnsRecordRequest
{
    /**
     * @param DnsRecordType|null $type     Record type — omit to leave unchanged
     * @param string|null        $name     Fully-qualified record name — omit to leave unchanged
     * @param string|null        $content  Record value — omit to leave unchanged
     * @param int|null           $ttl      Time-to-live in seconds; 1 = automatic — omit to leave unchanged
     * @param bool|null          $proxied  Whether traffic is proxied through Cloudflare — omit to leave unchanged
     * @param string|null        $comment  Human-readable note — omit to leave unchanged
     * @param string[]|null      $tags     Tags — omit to leave unchanged, pass [] to clear
     * @param int|null           $priority Priority for MX/SRV records — omit to leave unchanged
     */
    public function __construct(
        public readonly ?DnsRecordType $type = null,
        public readonly ?string $name = null,
        public readonly ?string $content = null,
        public readonly ?int $ttl = null,
        public readonly ?bool $proxied = null,
        public readonly ?string $comment = null,
        public readonly ?array $tags = null,
        public readonly ?int $priority = null,
    ) {}

    public function toArray(): array
    {
        $data = [];

        if ($this->type !== null) {
            $data['type'] = $this->type->value;
        }
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->content !== null) {
            $data['content'] = $this->content;
        }
        if ($this->ttl !== null) {
            $data['ttl'] = $this->ttl;
        }
        if ($this->proxied !== null) {
            $data['proxied'] = $this->proxied;
        }
        if ($this->comment !== null) {
            $data['comment'] = $this->comment;
        }
        if ($this->tags !== null) {
            $data['tags'] = $this->tags;
        }
        if ($this->priority !== null) {
            $data['priority'] = $this->priority;
        }

        return $data;
    }
}
