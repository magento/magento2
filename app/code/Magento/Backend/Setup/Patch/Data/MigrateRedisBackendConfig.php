<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Setup\Patch\Data;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Migrate Redis backend configuration from full class names to simple identifiers
 *
 * Automatically updates env.php during setup:upgrade:
 * - 'Magento\\Framework\\Cache\\Backend\\Redis' → 'redis'
 * - 'Magento\\Framework\\Cache\\Backend\\Valkey' → 'valkey'
 */
class MigrateRedisBackendConfig implements DataPatchInterface
{
    /**
     * @var Writer
     */
    private Writer $configWriter;

    /**
     * @var DeploymentConfig
     */
    private DeploymentConfig $deploymentConfig;

    /**
     * @param Writer $configWriter
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        Writer $configWriter,
        DeploymentConfig $deploymentConfig
    ) {
        $this->configWriter = $configWriter;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        // Migration map: Legacy full class name strings => new simple identifiers
        // These are not actual classes - they're legacy string identifiers in env.php
        $migrationMap = [
            // phpcs:ignore Magento2.PHP.LiteralNamespaces.LiteralClassUsage
            'Magento\\Framework\\Cache\\Backend\\Redis' => 'redis',
            // phpcs:ignore Magento2.PHP.LiteralNamespaces.LiteralClassUsage
            'Magento\\Framework\\Cache\\Backend\\Valkey' => 'valkey',
        ];

        // Get current cache configuration from env.php
        $cacheConfig = $this->deploymentConfig->get('cache');

        if (!$cacheConfig || !isset($cacheConfig['frontend'])) {
            // No cache frontend configuration - nothing to migrate
            return $this;
        }

        $configUpdates = [];
        $migrated = false;

        // Check and migrate each cache frontend
        foreach ($cacheConfig['frontend'] as $frontendName => $frontendConfig) {
            if (isset($frontendConfig['backend']) && isset($migrationMap[$frontendConfig['backend']])) {
                $oldValue = $frontendConfig['backend'];
                $newValue = $migrationMap[$oldValue];

                if (!isset($configUpdates['cache'])) {
                    $configUpdates['cache'] = $cacheConfig;
                }
                $configUpdates['cache']['frontend'][$frontendName]['backend'] = $newValue;
                $migrated = true;
            }
        }

        // Apply updates if any migrations occurred
        if ($migrated) {
            $this->configWriter->saveConfig(
                [ConfigFilePool::APP_ENV => $configUpdates],
                true
            );
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
