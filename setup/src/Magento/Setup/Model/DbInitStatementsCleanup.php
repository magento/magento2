<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Setup\Model;

use Magento\Framework\App\DeploymentConfig\Reader as ConfigReader;
use Magento\Framework\App\DeploymentConfig\Writer as ConfigWriter;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\DB\Helper\InitStatementsCleanup;

/**
 * Clean up deprecated SET NAMES utf8 from database connection initStatements in env.php
 *
 * This class is used during setup:upgrade to remove deprecated 'SET NAMES utf8;' statements
 * from all database connections in env.php configuration.
 */
class DbInitStatementsCleanup
{
    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var InitStatementsCleanup
     */
    private $initStatementsCleanup;

    /**
     * Constructor
     *
     * @param ConfigWriter $configWriter
     * @param ConfigReader $configReader
     * @param InitStatementsCleanup $initStatementsCleanup
     */
    public function __construct(
        ConfigWriter $configWriter,
        ConfigReader $configReader,
        InitStatementsCleanup $initStatementsCleanup
    ) {
        $this->configWriter = $configWriter;
        $this->configReader = $configReader;
        $this->initStatementsCleanup = $initStatementsCleanup;
    }

    /**
     * Execute the cleanup of deprecated SET NAMES utf8 from env.php
     *
     * This method:
     * 1. Reads the current env.php configuration
     * 2. Processes all database connections to remove 'SET NAMES utf8;'
     * 3. Saves the updated configuration back to env.php if any changes were made
     *
     * Examples:
     * - 'initStatements' => 'SET NAMES utf8;' -> entire initStatements key is removed
     * - 'initStatements' => 'SET NAMES utf8; SET lock_wait_timeout=120;'
     *   -> becomes 'initStatements' => 'SET lock_wait_timeout=120;'
     *
     * @return bool True if configuration was modified and saved
     */
    public function execute(): bool
    {
        $config = $this->configReader->load(ConfigFilePool::APP_ENV);

        // Process all database connections
        $modified = $this->initStatementsCleanup->processEnvConfig($config);

        // Save the configuration if any changes were made
        if ($modified) {
            $this->configWriter->saveConfig([ConfigFilePool::APP_ENV => $config], true);
        }

        return $modified;
    }
}
