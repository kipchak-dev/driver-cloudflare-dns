<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns;

use InvalidArgumentException;
use Kipchak\Core\Interfaces;
use Kipchak\Driver\CloudflareDns\Library\CloudflareDnsClient;
use Kipchak\Driver\Config\Config;
use Kipchak\Driver\Http\Library\Http as HttpClient;
use Psr\Container\ContainerInterface;

class CloudflareDns implements Interfaces\Driver
{
    protected static ?ContainerInterface $container = null;

    public static function initialise(ContainerInterface $container, ?string $configFileName = null): void
    {
        self::$container = $container;
        self::set($configFileName);
    }

    public static function set(?string $configFileName = null): void
    {
        $configFileName = $configFileName ?? 'kipchak.cloudflare-dns';
        $config = Config::get($configFileName);

        if (empty($config['connections'])) {
            return;
        }

        foreach ($config['connections'] as $connectionName => $connectionDetails) {
            self::$container->set(
                'drivers.dns.cloudflare.' . $connectionName,
                function (ContainerInterface $c) use ($connectionName, $connectionDetails): CloudflareDnsClient {
                    $apiToken = $connectionDetails['api_token'] ?? null;

                    if ($apiToken === null || $apiToken === '') {
                        throw new InvalidArgumentException(
                            'Cloudflare DNS connection "' . $connectionName . '" requires an api_token.'
                        );
                    }

                    $baseUrl = $connectionDetails['base_url'] ?? 'https://api.cloudflare.com/client/v4';

                    return new CloudflareDnsClient($apiToken, new HttpClient(), $baseUrl);
                }
            );
        }
    }

    public static function get(?string $configKey = null): CloudflareDnsClient
    {
        return self::$container->get('drivers.dns.cloudflare.' . ($configKey ?? 'default'));
    }
}
