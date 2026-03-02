<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Enum;

enum DnsRecordType: string
{
    case A      = 'A';
    case AAAA   = 'AAAA';
    case CAA    = 'CAA';
    case CERT   = 'CERT';
    case CNAME  = 'CNAME';
    case DNSKEY = 'DNSKEY';
    case DS     = 'DS';
    case HTTPS  = 'HTTPS';
    case LOC    = 'LOC';
    case MX     = 'MX';
    case NAPTR  = 'NAPTR';
    case NS     = 'NS';
    case PTR    = 'PTR';
    case SMIMEA = 'SMIMEA';
    case SOA    = 'SOA';
    case SRV    = 'SRV';
    case SSHFP  = 'SSHFP';
    case SVCB   = 'SVCB';
    case TLSA   = 'TLSA';
    case TXT    = 'TXT';
    case URI    = 'URI';
}
