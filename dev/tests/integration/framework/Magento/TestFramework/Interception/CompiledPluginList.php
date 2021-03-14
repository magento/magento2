<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Interception;

use Magento\Framework\CompiledInterception\Generator\FileCache;
use Magento\Framework\CompiledInterception\Generator\NoSerialize;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Interception\Definition\Runtime as InterceptionDefinitionRuntime;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\Interception\PluginList\PluginList as OriginalPluginList;
use Magento\Framework\ObjectManager\Config\Reader\Dom;
use Magento\Framework\ObjectManager\Definition\Runtime as ObjectManagerDefinitionRuntime;
use Magento\Framework\ObjectManager\Relations\Runtime as ObjectManagerRelationsRuntime;
use Magento\TestFramework\ObjectManager;

/**
 * Provides compiled plugin list configuration.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CompiledPluginList extends \Magento\Framework\CompiledInterception\Generator\CompiledPluginList
{
    /**
     * @var PluginList
     */
    private $pluginList;

    /**
     * @param OriginalPluginList $unused
     */
    public function __construct(
        OriginalPluginList $unused
    ) {
        $objectManager = ObjectManager::getInstance();
        $this->pluginList = $objectManager->create(
            PluginList::class,
            [
                $objectManager->get(Dom::class),
                $objectManager->get(ScopeInterface ::class),
                $objectManager->get(FileCache ::class),
                $objectManager->get(ObjectManagerRelationsRuntime ::class),
                $objectManager->get(ConfigInterface::class),
                $objectManager->get(InterceptionDefinitionRuntime ::class),
                $objectManager,
                $objectManager->get(ObjectManagerDefinitionRuntime ::class),
                ['global'],
                'compiled_plugins',
                $objectManager->get(NoSerialize ::class),
            ]
        );
        parent::__construct($this->pluginList);
    }

    /**
     * Reset internal cache
     */
    public function reset()
    {
        $this->pluginList->reset();
    }
}
