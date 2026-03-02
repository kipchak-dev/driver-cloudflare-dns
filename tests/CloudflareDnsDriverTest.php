<?php

declare(strict_types=1);

namespace Kipchak\Driver\CloudflareDns\Tests;

use InvalidArgumentException;
use Kipchak\Driver\CloudflareDns\CloudflareDns;
use Kipchak\Driver\CloudflareDns\Library\CloudflareDnsClient;
use Kipchak\Driver\Config\Config;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;

final class CloudflareDnsDriverTest extends TestCase
{
    public function testRegistersOneContainerEntryPerConnection(): void
    {
        $container = $this->primeDriver([
            'connections' => [
                'default'   => ['api_token' => 'token-1'],
                'secondary' => ['api_token' => 'token-2'],
            ],
        ]);

        $this->assertTrue($container->has('drivers.dns.cloudflare.default'));
        $this->assertTrue($container->has('drivers.dns.cloudflare.secondary'));
    }

    public function testGetReturnsCloudfareDnsClientInstance(): void
    {
        $this->primeDriver([
            'connections' => [
                'default' => ['api_token' => 'test-token'],
            ],
        ]);

        $this->assertInstanceOf(CloudflareDnsClient::class, CloudflareDns::get());
        $this->assertInstanceOf(CloudflareDnsClient::class, CloudflareDns::get('default'));
    }

    public function testMissingApiTokenThrowsInvalidArgumentException(): void
    {
        $this->primeDriver([
            'connections' => [
                'default' => ['api_token' => ''],
            ],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/api_token/');

        CloudflareDns::get('default');
    }

    public function testEmptyConnectionsDoesNotRegisterAnyEntries(): void
    {
        $container = $this->primeDriver([]);

        $this->assertFalse($container->has('drivers.dns.cloudflare.default'));
    }

    public function testCustomBaseUrlIsAccepted(): void
    {
        $container = $this->primeDriver([
            'connections' => [
                'default' => [
                    'api_token' => 'test-token',
                    'base_url'  => 'https://custom.api.example.com/v4',
                ],
            ],
        ]);

        // Resolving the entry should not throw — the base_url is accepted
        $this->assertInstanceOf(CloudflareDnsClient::class, CloudflareDns::get('default'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function primeDriver(array $config): TestContainer
    {
        $configContainer = new TestContainer();
        $configContainer->set('drivers.config', ['kipchak.cloudflare-dns' => $config]);
        $this->setStaticProperty(Config::class, 'container', $configContainer);

        $driverContainer = new TestContainer();
        CloudflareDns::initialise($driverContainer);

        return $driverContainer;
    }

    private function setStaticProperty(string $class, string $property, mixed $value): void
    {
        $reflection = new ReflectionClass($class);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue(null, $value);
    }
}

// ---------------------------------------------------------------------------
// Test doubles
// ---------------------------------------------------------------------------

final class TestContainer implements ContainerInterface
{
    private array $entries = [];

    public function set(string $id, mixed $value): void
    {
        $this->entries[$id] = $value;
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new TestNotFoundException('Service not found: ' . $id);
        }

        $entry = $this->entries[$id];

        return is_callable($entry) ? $entry($this) : $entry;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->entries);
    }
}

final class TestNotFoundException extends \RuntimeException implements NotFoundExceptionInterface {}
