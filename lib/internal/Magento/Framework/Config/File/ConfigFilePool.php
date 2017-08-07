<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\File;

/**
 * Stores file key to file name config
 * @api
 */
class ConfigFilePool
{
    const APP_CONFIG = 'app_config';
    const APP_ENV = 'app_env';

    /**
     * @deprecated Magento does not support custom config file pools since 2.2.0 version
     */
    const LOCAL = 'local';

    /**
     * @deprecated Magento does not support custom config file pools since 2.2.0 version
     */
    const DIST = 'dist';

    /**
     * Default files for configuration
     *
     * @var array
     */
    private $applicationConfigFiles = [
        self::APP_CONFIG => 'config.php',
        self::APP_ENV => 'env.php',
    ];

    /**
     * Initial files for configuration
     *
     * @var array
     * @deprecated 2.2.0 Magento does not support custom config file pools since 2.2.0 version
     * @since 2.1.3
     */
    private $initialConfigFiles = [
        self::DIST => [
            self::APP_CONFIG => 'config.dist.php',
            self::APP_ENV => 'env.dist.php',
        ],
        self::LOCAL => [
            self::APP_CONFIG => 'config.local.php',
            self::APP_ENV => 'env.local.php',
        ]
    ];

    /**
     * Constructor
     *
     * @param array $additionalConfigFiles
     */
    public function __construct($additionalConfigFiles = [])
    {
        $this->applicationConfigFiles = array_merge($this->applicationConfigFiles, $additionalConfigFiles);
    }

    /**
     * Returns application config files.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->applicationConfigFiles;
    }

    /**
     * Returns file path by config key
     *
     * @param string $fileKey
     * @return string
     * @throws \Exception
     */
    public function getPath($fileKey)
    {
        if (!isset($this->applicationConfigFiles[$fileKey])) {
            throw new \Exception('File config key does not exist.');
        }
        return $this->applicationConfigFiles[$fileKey];
    }

    /**
     * Returns application initial config files.
     *
     * @return array
     * @deprecated 2.2.0 Magento does not support custom config file pools since 2.2.0 version
     * @since 2.1.3
     */
    public function getInitialFilePools()
    {
        return $this->initialConfigFiles;
    }

    /**
     * Retrieve all config file pools.
     *
     * @param string $pool
     * @return array
     * @deprecated 2.2.0 Magento does not support custom config file pools since 2.2.0 version
     * @since 2.1.3
     */
    public function getPathsByPool($pool)
    {
        return $this->initialConfigFiles[$pool];
    }
}
