<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Code\Generator;

use Magento\Framework\App\Area;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Interception\Cache\CompiledConfig;
use Magento\Framework\Interception\Config\Config as InterceptionConfig;
use Magento\Setup\Module\Di\Code\Reader\Type;

class InterceptionConfigurationBuilder
{
    /**
     * Area code list: global, frontend, etc.
     *
     * @var array
     */
    private $areaCodesList = [];

    /**
     * @var InterceptionConfig
     */
    private $interceptionConfig;

    /**
     * @var PluginList
     */
    private $pluginList;

    /**
     * @var Type
     */
    private $typeReader;

    /**
     * @var Manager
     */
    private $cacheManager;

    /**
     * @param InterceptionConfig $interceptionConfig
     * @param PluginList $pluginList
     * @param Type $typeReader
     * @param Manager $cacheManager
     */
    public function __construct(
        InterceptionConfig $interceptionConfig,
        PluginList $pluginList,
        Type $typeReader,
        Manager $cacheManager
    ) {
        $this->interceptionConfig = $interceptionConfig;
        $this->pluginList = $pluginList;
        $this->typeReader = $typeReader;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Adds area code
     *
     * @param string $areaCode
     * @return void
     */
    public function addAreaCode($areaCode)
    {
        if (empty($this->areaCodesList[$areaCode])) {
            $this->areaCodesList[] = $areaCode;
        }
    }

    /**
     * Builds interception configuration for all defined classes
     *
     * @param array $definedClasses
     * @return array
     */
    public function getInterceptionConfiguration($definedClasses)
    {
        $interceptedInstances = $this->getInterceptedClasses($definedClasses);
        $inheritedConfig = $this->getPluginsList($interceptedInstances);
        $mergedAreaPlugins = $this->mergeAreaPlugins($inheritedConfig);
        $interceptedMethods = $this->getInterceptedMethods($mergedAreaPlugins);

        return $interceptedMethods;
    }

    /**
     * Get intercepted instances from defined class list
     *
     * @param array $definedClasses
     * @return array
     */
    private function getInterceptedClasses($definedClasses)
    {
        $intercepted = [];
        foreach ($definedClasses as $definedClass) {
            if ($this->interceptionConfig->hasPlugins($definedClass) && $this->typeReader->isConcrete($definedClass)) {
                $intercepted[] = $definedClass;
            }
        }
        return $intercepted;
    }

    /**
     * Returns plugin list:
     * ['concrete class name' => ['plugin name' => [instance => 'instance name', 'order' => 'Order Number']]]
     *
     * @param array $interceptedInstances
     * @return array
     */
    private function getPluginsList($interceptedInstances)
    {
        $this->cacheManager->setEnabled([CompiledConfig::TYPE_IDENTIFIER], true);
        $this->pluginList->setInterceptedClasses($interceptedInstances);

        $inheritedConfig = [];
        foreach ($this->areaCodesList as $areaKey) {
            $scopePriority = [Area::AREA_GLOBAL];
            $pluginListCloned = clone $this->pluginList;
            if ($areaKey != Area::AREA_GLOBAL) {
                $scopePriority[] = $areaKey;
                $pluginListCloned->setScopePriorityScheme($scopePriority);
            }
            $key = implode('', $scopePriority);
            $inheritedConfig[$key] = $this->filterNullInheritance($pluginListCloned->getPluginsConfig());
        }
        return $inheritedConfig;
    }

    /**
     * Filters plugin inheritance list for instances without plugins, and abstract/interface
     *
     * @param array $pluginInheritance
     * @return array
     */
    private function filterNullInheritance($pluginInheritance)
    {
        $filteredData = [];
        foreach ($pluginInheritance as $instance => $plugins) {
            if ($plugins === null || !$this->typeReader->isConcrete($instance)) {
                continue;
            }

            $pluginInstances = [];
            foreach ($plugins as $plugin) {
                if (in_array($plugin['instance'], $pluginInstances)) {
                    continue;
                }
                $pluginInstances[] = $plugin['instance'];
            }
            $filteredData[$instance] = $pluginInstances;
        }

        return $filteredData;
    }

    /**
     * Merge plugins in areas
     *
     * @param array $inheritedConfig
     * @return array
     */
    private function mergeAreaPlugins($inheritedConfig)
    {
        $mergedConfig = [];
        foreach ($inheritedConfig as $configuration) {
            $mergedConfig = array_merge_recursive($mergedConfig, $configuration);
        }
        foreach ($mergedConfig as &$plugins) {
            $plugins = array_unique($plugins);
        }

        return $mergedConfig;
    }

    /**
     * Returns interception configuration with plugin methods
     *
     * @param array $interceptionConfiguration
     * @return array
     */
    private function getInterceptedMethods($interceptionConfiguration)
    {
        $pluginDefinitionList = new \Magento\Framework\Interception\Definition\Runtime();
        foreach ($interceptionConfiguration as &$plugins) {
            $pluginsMethods = [];
            foreach ($plugins as $plugin) {
                $pluginsMethods = array_unique(
                    array_merge($pluginsMethods, array_keys($pluginDefinitionList->getMethodList($plugin)))
                );
            }
            $plugins = $pluginsMethods;
        }
        return $interceptionConfiguration;
    }
}
