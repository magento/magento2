<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Interception\PluginList\PluginList;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Framework\ObjectManager\Relations\Runtime as ObjectManagerRelationsRuntime;
use Magento\Framework\Interception\Definition\Runtime as InterceptionDefinitionRuntime;
use Magento\Framework\ObjectManager\Definition\Runtime as ObjectManagerDefinitionRuntime;

/**
 * Class CompiledPluginList
 */
class CompiledPluginList extends PluginList
{
    /**
     * CompiledPluginList constructor.
     * @param ObjectManager $objectManager
     * @param ScopeInterface $scope
     * @param null|ReaderInterface $reader
     * @param null|ConfigInterface $omConfig
     * @param null|string $cachePath
     */
    public function __construct(
        ObjectManager $objectManager,
        ScopeInterface $scope,
        ReaderInterface $reader = null,
        ConfigInterface $omConfig = null,
        $cachePath = null
    ) {
        if (!$reader || !$omConfig) {
            $reader = $objectManager->get(Dom::class);
            $omConfig = $objectManager->get(ConfigInterface::class);
        }
        parent::__construct(
            $reader,
            $scope,
            new FileCache($cachePath),
            new ObjectManagerRelationsRuntime(),
            $omConfig,
            new InterceptionDefinitionRuntime(),
            $objectManager,
            new ObjectManagerDefinitionRuntime(),
            ['global'],
            'compiled_plugins',
            new NoSerialize()
        );
    }

    /**
     * Retrieve plugin Instance
     *
     * @param string $type
     * @param string $code
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPlugin($type, $code)
    {
        return null;
    }

    /**
     * Get class of a plugin
     *
     * @param string $type
     * @param string $code
     * @return mixed
     */
    public function getPluginType($type, $code)
    {
        return $this->_inherited[$type][$code]['instance'];
    }

    /**
     * Set current scope
     *
     * @param ScopeInterface $scope
     */
    public function setScope(ScopeInterface $scope)
    {
        $this->_configScope = $scope;
    }
}
