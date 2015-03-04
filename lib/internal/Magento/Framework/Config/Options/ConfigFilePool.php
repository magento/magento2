<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Stores file key to file name config
 */
class ConfigFilePool 
{
    const APP_CONFIG = 'app_config';

    /**
     * Default application config
     *
     * @var array
     */
    private $applicationConfigFiles = [
        // TODO: add DirectoryList
        self::APP_CONFIG => 'app/etc/config.php'
    ];

    public function __construct($additionalConfigFiles)
    {
        // TODO: implement it
    }

    /**
     * Returns application config files
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
     * @param string $key
     * @return string
     * @throws Exception
     */
    public function getPath($key)
    {
        if (!isset($this->applicationConfigFiles[$key])) {
            throw new \Exception('');
        }
        return $this->applicationConfigFiles[$key];
    }

}