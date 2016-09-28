<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager\ConfigLoader;

use Magento\Framework\Json\JsonInterface;
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
     * @var JsonInterface
     */
    private $json;

    /**
     * {inheritdoc}
     */
    public function load($area)
    {
        if (isset($this->configCache[$area])) {
            return $this->configCache[$area];
        }
        $this->configCache[$area] = $this->getJson()->decode(\file_get_contents(self::getFilePath($area)));
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

    /**
     * Get json encoder/decoder
     *
     * @return JsonInterface
     * @deprecated
     */
    private function getJson()
    {
        if ($this->json === null) {
            $this->json = new \Magento\Framework\Json\Json();
        }
        return $this->json;
    }
}
