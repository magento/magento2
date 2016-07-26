<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager\ConfigLoader;

use Magento\Framework\ObjectManager\ConfigLoaderInterface;

/**
 * Class Compiled returns configuration cache information
 */
class Compiled implements ConfigLoaderInterface
{
    /**
     * Global config
     *
     * @var array
     */
    private $configCache = [];

    /**
     * {inheritdoc}
     */
    public function load($area)
    {
        if (isset($this->configCache[$area])) {
            return $this->configCache[$area];
        }
        $filePath = self::getFilePath($area);
        if (\file_exists($filePath)) {
            $this->configCache[$area] = \unserialize(\file_get_contents($filePath));
            return $this->configCache[$area];
        }
        return [];
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
