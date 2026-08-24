<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Cache;

use Magento\Framework\App\Cache\Frontend\Factory;
use Magento\Framework\Cache\FrontendInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

abstract class CacheFrontendTestCase extends TestCase
{
    protected Factory $cacheFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheFactory = Bootstrap::getObjectManager()->get(Factory::class);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    protected function createFrontend(
        array $configuration,
        string $configurationName,
        string $prefix = 'IT'
    ): FrontendInterface {
        $configuration['id_prefix'] = $prefix . '_' . strtoupper(
            str_replace('-', '_', $configurationName)
        ) . '_';
        $this->createCacheDirectories($configuration);

        return $this->cacheFactory->create($configuration);
    }

    protected function cacheId(
        string $configurationName,
        string $name,
        string $prefix = 'integration'
    ): string {
        return $prefix . '_' . str_replace('-', '_', $configurationName) . '_' . $name . '_' . uniqid();
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function createCacheDirectories(array $configuration): void
    {
        $backendOptions = $configuration['backend_options'] ?? [];
        if (is_string($backendOptions['cache_dir'] ?? null)) {
            $this->ensureDirectory($backendOptions['cache_dir']);
        }

        $localOptions = $backendOptions['local_backend_options'] ?? [];
        if (is_array($localOptions) && is_string($localOptions['cache_dir'] ?? null)) {
            $this->ensureDirectory($localOptions['cache_dir']);
        }
    }

    private function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create cache directory: ' . $directory);
        }
    }
}
