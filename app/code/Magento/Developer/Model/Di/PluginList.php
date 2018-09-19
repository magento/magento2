<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Model\Di;

use Magento\Framework\Interception;
use Magento\Framework\Interception\DefinitionInterface;

/**
 * Provides plugin list configuration
 */
class PluginList extends Interception\PluginList\PluginList
{
    /**#@+
     * Constants for the plugin types
     */
    const PLUGIN_TYPE_BEFORE = 'before';
    const PLUGIN_TYPE_AROUND = 'around';
    const PLUGIN_TYPE_AFTER = 'after';
    /**#@-*/

    /**#@-*/
    private $pluginList = [
       self::PLUGIN_TYPE_BEFORE => [],
       self::PLUGIN_TYPE_AROUND => [],
       self::PLUGIN_TYPE_AFTER  => []
    ];

    /**
     * Mapping of plugin type codes to plugin types
     * @var array
     */
    private $pluginTypeMapping = [
        DefinitionInterface::LISTENER_AROUND => self::PLUGIN_TYPE_AROUND,
        DefinitionInterface::LISTENER_BEFORE => self::PLUGIN_TYPE_BEFORE,
        DefinitionInterface::LISTENER_AFTER => self::PLUGIN_TYPE_AFTER
    ];

    /**
     * Returns plugins config
     *
     * @return array
     */
    public function getPluginsConfig()
    {
        $this->_loadScopedData();

        return $this->_inherited;
    }

    /**
     * Sets scope priority scheme
     *
     * @param array $areaCodes
     *
     * @return void
     */
    public function setScopePriorityScheme($areaCodes)
    {
        $this->_scopePriorityScheme = $areaCodes;
    }

    /**
     * Whether scope code is current scope code
     *
     * @param string $scopeCode
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function isCurrentScope($scopeCode)
    {
        return false;
    }

    /**
     * Load the plugins information
     *
     * @param string $type
     * @return array
     */
    private function getPlugins($type)
    {
        $this->_loadScopedData();
        if (!isset($this->_inherited[$type]) && !array_key_exists($type, $this->_inherited)) {
            $this->_inheritPlugins($type);
        }
        return $this->_inherited[$type];
    }

    /**
     * Return the list of plugins for the class
     *
     * @param string $className
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getPluginsListByClass($className)
    {
        $this->getPlugins($className);
        if (!isset($this->_inherited[$className])) {
            return $this->pluginList;
        }

        foreach ($this->_inherited[$className] as $plugin) {
            foreach ($this->_definitions->getMethodList($plugin['instance']) as $pluginMethod => $methodTypes) {
                $this->addPluginToList(
                    $plugin['instance'],
                    $pluginMethod,
                    $methodTypes,
                    DefinitionInterface::LISTENER_AROUND
                );
                $this->addPluginToList(
                    $plugin['instance'],
                    $pluginMethod,
                    $methodTypes,
                    DefinitionInterface::LISTENER_BEFORE
                );
                $this->addPluginToList(
                    $plugin['instance'],
                    $pluginMethod,
                    $methodTypes,
                    DefinitionInterface::LISTENER_AFTER
                );
            }
        }
        return $this->pluginList;
    }

    /**
     * Add plugin to the appropriate type bucket
     *
     * @param string $pluginInstance
     * @param string $pluginMethod
     * @param int $methodTypes
     * @param int $typeCode
     * @return void
     */
    private function addPluginToList($pluginInstance, $pluginMethod, $methodTypes, $typeCode)
    {
        if ($methodTypes & $typeCode) {
            if (!array_key_exists($pluginInstance, $this->pluginList[$this->pluginTypeMapping[$typeCode]])) {
                $this->pluginList[$this->pluginTypeMapping[$typeCode]][$pluginInstance] = [];
            }

            if (!in_array($pluginMethod, $this->pluginList[$this->pluginTypeMapping[$typeCode]][$pluginInstance])) {
                $this->pluginList[$this->pluginTypeMapping[$typeCode]][$pluginInstance][] = $pluginMethod;
            }
        }
    }
}
