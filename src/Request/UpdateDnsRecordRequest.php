<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Request;

use Kipchak\Driver\CloudflareDns\Enum\DnsRecordType;

/**
 * Full replacement of an existing DNS record (PUT).
 * All fields are sent to Cloudflare, so every property must be provided.
 */
final class UpdateDnsRecordRequest
{
    /**
     * @param DnsRecordType $type    Record type (A, AAAA, CNAME, MX, TXT, …)
     * @param string        $name    Fully-qualified record name, e.g. "api.example.com"
     * @param string        $content Record value (IP address, hostname, text, …)
     * @param int           $ttl     Time-to-live in seconds; 1 = automatic
     * @param bool          $proxied Whether traffic is proxied through Cloudflare (A/AAAA/CNAME only)
     * @param string|null   $comment Optional human-readable note
     * @param string[]      $tags    Optional tags for filtering in the dashboard
     * @param int|null      $priority Priority value required for MX and SRV records
     */
    public function __construct(
        public readonly DnsRecordType $type,
        public readonly string $name,
        public readonly string $content,
        public readonly int $ttl = 1,
        public readonly bool $proxied = false,
        public readonly ?string $comment = null,
        public readonly array $tags = [],
        public readonly ?int $priority = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'type'    => $this->type->value,
            'name'    => $this->name,
            'content' => $this->content,
            'ttl'     => $this->ttl,
            'proxied' => $this->proxied,
        ];

        if ($this->comment !== null) {
            $data['comment'] = $this->comment;
        }
        if (!empty($this->tags)) {
            $data['tags'] = $this->tags;
        }
        if ($this->priority !== null) {
            $data['priority'] = $this->priority;
        }

        return $data;
    }
}
