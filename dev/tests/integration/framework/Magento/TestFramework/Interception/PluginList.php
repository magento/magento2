<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Interception;

use Magento\Framework\Interception\ConfigLoaderInterface;
use Magento\Framework\Interception\PluginListGenerator;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides plugin list configuration
 */
class PluginList extends \Magento\Framework\Interception\PluginList\PluginList
{
    /**
     * @var array
     */
    protected $_originScopeScheme = [];

    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\ReaderInterface $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\ObjectManager\RelationsInterface $relations
     * @param \Magento\Framework\ObjectManager\ConfigInterface $omConfig
     * @param \Magento\Framework\Interception\DefinitionInterface $definitions
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\ObjectManager\DefinitionInterface $classDefinitions
     * @param array $scopePriorityScheme
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     * @param ConfigLoaderInterface|null $configLoader
     * @param PluginListGenerator|null $pluginListGenerator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Config\ReaderInterface $reader,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\ObjectManager\RelationsInterface $relations,
        \Magento\Framework\ObjectManager\ConfigInterface $omConfig,
        \Magento\Framework\Interception\DefinitionInterface $definitions,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\ObjectManager\DefinitionInterface $classDefinitions,
        array $scopePriorityScheme,
        $cacheId = 'plugins',
        SerializerInterface $serializer = null,
        ConfigLoaderInterface $configLoader = null,
        PluginListGenerator $pluginListGenerator = null
    ) {
        parent::__construct(
            $reader,
            $configScope,
            $cache,
            $relations,
            $omConfig,
            $definitions,
            $objectManager,
            $classDefinitions,
            $scopePriorityScheme,
            $cacheId,
            $serializer,
            $configLoader,
            $pluginListGenerator
        );
        $this->_originScopeScheme = $this->_scopePriorityScheme;
    }

    /**
     * Reset internal cache
     */
    public function reset()
    {
        $this->_scopePriorityScheme = $this->_originScopeScheme;
        $this->_data = [];
        $this->_loadedScopes = [];
    }
}
