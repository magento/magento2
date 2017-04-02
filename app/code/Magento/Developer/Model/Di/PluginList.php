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
    /**
     * @var array
     */
    private $pluginList = [
       'before' => [],
       'around' => [],
       'after'  => []
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

        foreach ($this->_inherited[$className] as $pluginKey => $plugin) {
            foreach ($this->_definitions->getMethodList($plugin['instance']) as $pluginMethod => $methodTypes) {
                if ($methodTypes & DefinitionInterface::LISTENER_AROUND) {
                    if (!array_key_exists($plugin['instance'], $this->pluginList['around'])) {
                        $this->pluginList['around'][$plugin['instance']] = [];
                    }
                    $this->pluginList['around'][$plugin['instance']][] = $pluginMethod ;
                }
                if ($methodTypes & DefinitionInterface::LISTENER_BEFORE) {
                    if (!array_key_exists($plugin['instance'], $this->pluginList['before'])) {
                        $this->pluginList['before'][$plugin['instance']] = [];
                    }
                    $this->pluginList['before'][$plugin['instance']][] = $pluginMethod ;

                }
                if ($methodTypes & DefinitionInterface::LISTENER_AFTER) {
                    if (!array_key_exists($plugin['instance'], $this->pluginList['after'])) {
                        $this->pluginList['after'][$plugin['instance']] = [];
                    }
                    $this->pluginList['after'][$plugin['instance']][] = $pluginMethod ;
                }
            }
        }
        return $this->pluginList;
    }
}

