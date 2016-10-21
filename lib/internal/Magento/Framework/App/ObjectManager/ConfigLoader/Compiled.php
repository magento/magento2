<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager\ConfigLoader;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Serialize;

class Compiled implements ConfigLoaderInterface
{
    /**
     * Global config
     *
     * @var array
     */
    private $configCache = [];

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * {inheritdoc}
     */
    public function load($area)
    {
        if (isset($this->configCache[$area])) {
            return $this->configCache[$area];
        }
        $this->configCache[$area] = $this->getSerializer()->unserialize(\file_get_contents(self::getFilePath($area)));
        return $this->configCache[$area];
    }

    /**
     * Returns path to compiled configuration
     *
     * @param string $area
     * @return string
     */
    public static function getFilePath($area)
    {
        $diPath = DirectoryList::getDefaultConfig()[DirectoryList::DI][DirectoryList::PATH];
        return BP . $diPath . '/' . $area . '.json';
    }

    /**
     * Get serializer
     *
     * @return SerializerInterface
     * @deprecated
     */
    private function getSerializer()
    {
        if (null === $this->serializer) {
            $this->serializer = new Serialize();
        }
        return $this->serializer;
    }
}
