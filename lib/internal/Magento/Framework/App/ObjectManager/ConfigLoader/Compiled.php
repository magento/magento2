<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager\ConfigLoader;

use Magento\Framework\ObjectManager\ConfigLoaderInterface;

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
