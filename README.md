# Cloudflare DNS Driver for Kipchak

A driver for managing Cloudflare DNS via the [Cloudflare API v4](https://developers.cloudflare.com/api/). Uses the Kipchak HTTP driver as its HTTP client — no additional HTTP library dependency.

Supports listing zones and creating, reading, updating (full and partial), and deleting DNS records, with fully typed request objects and result objects throughout.

#### Composer Package

`kipchak/driver-cloudflare-dns`

#### Sample Config File

Copy `sample.config.php` to your project's `config/` directory as `kipchak.cloudflare-dns.php`.

```php
// config/kipchak.cloudflare-dns.php
return [
    'connections' => [
        'default' => [
            'api_token' => env('CF_API_TOKEN', ''),
        ],
    ],
];
```

Set `CF_API_TOKEN` in your environment to a Cloudflare API Token with **Zone:Read** and **DNS:Edit** permissions.

## Registration

In `drivers/drivers.php`, register the driver after `Config` and `Http` (both are dependencies):

```php
use Kipchak\Driver\CloudflareDns\CloudflareDns;

CloudflareDns::initialise($container);
```

## Usage

```php
$cf = CloudflareDns::get();            // 'default' connection
// $cf = CloudflareDns::get('secondary'); // named connection
```

### Zones

```php
use Kipchak\Driver\CloudflareDns\Enum\ZoneStatus;
use Kipchak\Driver\CloudflareDns\Request\ListZonesRequest;

// List all zones — returns ZoneResult[]
$zones = $cf->listZones();

// Filter zones
$zones = $cf->listZones(new ListZonesRequest(
    name:   'example.com',
    status: ZoneStatus::Active,
));

// Get a single zone — returns ZoneResult
$zone = $cf->getZone('023e105f4ecef8ad9ca31a8372d0c353');

echo $zone->id;
echo $zone->name;
echo $zone->status->value;  // 'active'
echo implode(', ', $zone->nameServers);
```

### DNS Records

```php
use Kipchak\Driver\CloudflareDns\Enum\DnsRecordType;
use Kipchak\Driver\CloudflareDns\Request\CreateDnsRecordRequest;
use Kipchak\Driver\CloudflareDns\Request\ListDnsRecordsRequest;
use Kipchak\Driver\CloudflareDns\Request\PatchDnsRecordRequest;
use Kipchak\Driver\CloudflareDns\Request\UpdateDnsRecordRequest;

$zoneId = '023e105f4ecef8ad9ca31a8372d0c353';

// List all DNS records — returns DnsRecordResult[]
$records = $cf->listDnsRecords($zoneId);

// Filter records
$records = $cf->listDnsRecords($zoneId, new ListDnsRecordsRequest(
    type: DnsRecordType::A,
    name: 'api.example.com',
));

// Get a single record — returns DnsRecordResult
$record = $cf->getDnsRecord($zoneId, 'record-id-here');
echo $record->id;
echo $record->type->value;   // 'A'
echo $record->content;       // '192.0.2.1'

// Create a record — returns DnsRecordResult
$record = $cf->createDnsRecord($zoneId, new CreateDnsRecordRequest(
    type:    DnsRecordType::A,
    name:    'api.example.com',
    content: '192.0.2.1',
    ttl:     1,         // 1 = automatic
    proxied: false,
    comment: 'API server',
));

// Create an MX record (priority required)
$record = $cf->createDnsRecord($zoneId, new CreateDnsRecordRequest(
    type:     DnsRecordType::MX,
    name:     'example.com',
    content:  'mail.example.com',
    priority: 10,
));

// Fully replace a record (PUT) — returns DnsRecordResult
$record = $cf->updateDnsRecord($zoneId, 'record-id-here', new UpdateDnsRecordRequest(
    type:    DnsRecordType::A,
    name:    'api.example.com',
    content: '192.0.2.2',
    proxied: false,
));

// Partially update a record (PATCH) — returns DnsRecordResult
$record = $cf->patchDnsRecord($zoneId, 'record-id-here', new PatchDnsRecordRequest(
    content: '192.0.2.3',
));

// Delete a record (throws RuntimeException on failure)
$cf->deleteDnsRecord($zoneId, 'record-id-here');
```

## Type Reference

### Enums

| Enum | Values |
|---|---|
| `DnsRecordType` | `A` `AAAA` `CAA` `CERT` `CNAME` `DNSKEY` `DS` `HTTPS` `LOC` `MX` `NAPTR` `NS` `PTR` `SMIMEA` `SOA` `SRV` `SSHFP` `SVCB` `TLSA` `TXT` `URI` |
| `ZoneStatus` | `Active` `Pending` `Initializing` `Moved` |

### Request Objects

| Class | Used for |
|---|---|
| `ListZonesRequest` | Filtering `listZones()` |
| `ListDnsRecordsRequest` | Filtering `listDnsRecords()` |
| `CreateDnsRecordRequest` | `createDnsRecord()` — `type`, `name`, `content` required |
| `UpdateDnsRecordRequest` | `updateDnsRecord()` — full replace, all fields sent |
| `PatchDnsRecordRequest` | `patchDnsRecord()` — only non-null fields sent |

### Result Objects

| Class | Returned by |
|---|---|
| `ZoneResult` | `listZones()`, `getZone()` |
| `DnsRecordResult` | `listDnsRecords()`, `getDnsRecord()`, `createDnsRecord()`, `updateDnsRecord()`, `patchDnsRecord()` |

## Error Handling

All methods throw a `RuntimeException` when the Cloudflare API returns an error:

```
[CF <code>] <message> (HTTP <status>)
```

## What is a Kipchak Driver?

Kipchak Drivers connect Kipchak to external services via its DI container, with support for multiple named connections and lazy initialisation.

See the [Kipchak documentation](https://kipchak.dev) for more detail.
