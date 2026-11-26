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

/**
 * Base test case that builds real cache frontends from named configurations for integration tests.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class CacheFrontendTestCase extends TestCase
{
    /**
     * @var Factory
     */
    protected Factory $cacheFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheFactory = Bootstrap::getObjectManager()->get(Factory::class);
    }

    /**
     * Create a cache frontend for the given configuration.
     *
     * @param array $configuration
     * @param string $configurationName
     * @param string $prefix
     * @return FrontendInterface
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

    /**
     * Build a unique cache id for the given configuration and logical name.
     *
     * @param string $configurationName
     * @param string $name
     * @param string $prefix
     * @return string
     */
    protected function cacheId(
        string $configurationName,
        string $name,
        string $prefix = 'integration'
    ): string {
        return $prefix . '_' . str_replace('-', '_', $configurationName) . '_' . $name . '_' . uniqid();
    }

    /**
     * Ensure any configured cache directories exist before the backend is created.
     *
     * @param array $configuration
     * @return void
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

    /**
     * Create the given directory (recursively) if it does not already exist.
     *
     * @param string $directory
     * @return void
     */
    private function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create cache directory: ' . $directory);
        }
    }
}
