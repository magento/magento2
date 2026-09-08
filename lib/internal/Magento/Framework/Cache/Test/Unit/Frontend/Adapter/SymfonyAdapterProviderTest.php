<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapterProvider;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Serialize;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SymfonyAdapterProviderTest extends TestCase
{
    /**
     * @var SymfonyAdapterProvider
     */
    private SymfonyAdapterProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new SymfonyAdapterProvider(
            $this->createMock(Filesystem::class),
            $this->createMock(ResourceConnection::class),
            $this->createMock(Serialize::class)
        );
    }

    /**
     * Only backend types resolving to the filesystem adapter are reported as file-based.
     *
     * @param string $backendType
     * @param bool $expected
     * @return void
     */
    #[DataProvider('isFilesystemBackendDataProvider')]
    public function testIsFilesystemBackend(string $backendType, bool $expected): void
    {
        $this->assertSame($expected, $this->provider->isFilesystemBackend($backendType));
    }

    /**
     * @return array[]
     */
    public static function isFilesystemBackendDataProvider(): array
    {
        return [
            'file' => ['file', true],
            'file uppercase' => ['FILE', true],
            'redis' => ['redis', false],
            'valkey' => ['valkey', false],
            'memcached' => ['memcached', false],
            'libmemcached' => ['libmemcached', false],
            'database' => ['database', false],
            'apcu' => ['apcu', false],
            'two levels' => ['two_levels', false],
            'unknown falls back to filesystem' => ['Some\Unknown\Backend', true],
        ];
    }
}
