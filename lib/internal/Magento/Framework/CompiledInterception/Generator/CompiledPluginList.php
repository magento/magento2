<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Interception\PluginList\PluginList;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\Config\Reader\Dom;

class CompiledPluginList extends PluginList
{
    /**
     * CompiledPluginList constructor.
     * @param $objectManager ObjectManager
     * @param $scope
     * @param null $reader
     * @param null $omConfig
     * @param null $cachePath
     */
    public function __construct(
        $objectManager,
        $scope,
        $reader = null,
        $omConfig = null,
        $cachePath = null
    ) {
        if (!$reader || !$omConfig) {
            $reader = $objectManager->get(Dom::class);
            $omConfig = $objectManager->get(ConfigInterface::class);
        }
        parent::__construct(
            $reader,
            new StaticScope($scope),
            new FileCache($cachePath),
            new \Magento\Framework\ObjectManager\Relations\Runtime(),
            $omConfig,
            new \Magento\Framework\Interception\Definition\Runtime(),
            $objectManager,
            new \Magento\Framework\ObjectManager\Definition\Runtime(),
            ['first' => 'global'],
            $cacheId = 'compiled_plugins_' . $scope,
            new FileCache($cachePath)
        );
    }

    /**
     * Retrieve plugin Instance
     *
     * @param string $type
     * @param string $code
     * @return mixed
     */
    public function getPlugin($type, $code)
    {
        return null;
    }

    /**
     * @param $type
     * @param $code
     * @return mixed
     */
    public function getPluginType($type, $code)
    {
        return $this->_inherited[$type][$code]['instance'];
    }

}
