<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager\ConfigLoader;

class Compiled extends \Magento\Framework\App\ObjectManager\ConfigLoader
{
    /**
     * Global config
     *
     * @var array
     */
    private $configCache = [];

    /**
     * Compiled construct
     */
    public function __construct()
    {
    }

    /**
     * Load modules DI configuration
     *
     * @param string $area
     * @return array|mixed
     */
    public function load($area)
    {
        if (isset($this->configCache[$area])) {
            return $this->configCache[$area];
        }
        $this->configCache[$area] = \unserialize(\file_get_contents(self::getFilePath($area)));
        return $this->configCache[$area];
    }

    /**
     * Returns path to cached configuration
     *
     * @param string $area
     * @return string
     */
    public static function getFilePath($area)
    {
        return BP . '/var/di/' . $area . '.ser';
    }
}
