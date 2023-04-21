<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Deploy;

use Magento\Framework\App\DeploymentConfig\Reader as ConfigReader;
use Magento\Framework\App\DeploymentConfig\Writer as ConfigWriter;
use Magento\Framework\Config\File\ConfigFilePool;

/**
 * The purpose of this class is add sharding db connections to env file.
 */
class ShardingConfig
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
     * @param ConfigWriter $configWriter
     * @param ConfigReader $configReader
     */
    public function __construct(ConfigWriter $configWriter, ConfigReader $configReader)
    {
        $this->configWriter = $configWriter;
        $this->configReader = $configReader;
    }

    /**
     * Apply sharding database connection to env file
     */
    public function applyConfiguration()
    {
        $allDbData = include TESTS_INSTALLATION_DB_CONFIG_FILE;
        $config = $this->configReader->load(ConfigFilePool::APP_ENV);

        foreach ($allDbData as $connectionName => $dbData) {
            if (!isset($config['db']['connection'][$connectionName]) && $connectionName !== 'default') {
                $config['db']['connection'][$connectionName] = [
                    'host' => $dbData['host'],
                    'username' => $dbData['username'],
                    'password' => $dbData['password'],
                    'dbname' => $dbData['dbname'],
                    'model' => 'mysql4',
                    'engine' => 'innodb',
                    'initStatements' => 'SET NAMES utf8;',
                    'active' => '1'
                ];
                $config['resource'][$connectionName] = [
                    'connection' => $connectionName
                ];
                $this->configWriter->saveConfig([ConfigFilePool::APP_ENV => $config], true);
            }
        }
    }
}
