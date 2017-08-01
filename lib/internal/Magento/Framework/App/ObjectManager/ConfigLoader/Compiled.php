<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager\ConfigLoader;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * Class \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled
 *
 * @since 2.0.0
 */
class Compiled implements ConfigLoaderInterface
{
    /**
     * Global config
     *
     * @var array
     * @since 2.0.0
     */
    private $configCache = [];

    /**
     * {inheritdoc}
     * @since 2.0.0
     */
    public function load($area)
    {
        if (isset($this->configCache[$area])) {
            return $this->configCache[$area];
        }
        $diConfiguration = include(self::getFilePath($area));
        $this->configCache[$area] = $diConfiguration;
        return $this->configCache[$area];
    }

    /**
     * Returns path to compiled configuration
     *
     * @param string $area
     * @return string
     * @since 2.0.0
     */
    public static function getFilePath($area)
    {
        $diPath = DirectoryList::getDefaultConfig()[DirectoryList::GENERATED_METADATA][DirectoryList::PATH];
        return BP . '/' . $diPath . '/' . $area . '.php';
    }
}
