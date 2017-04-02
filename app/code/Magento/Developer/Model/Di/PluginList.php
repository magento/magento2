<?php
/**
 * Created by PhpStorm.
 * @author Andra Lungu <andra.lungu@bitbull.it>
 * Date: 01/04/17
 * Time: 14.02
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
        DefinitionInterface::LISTENER_BEFORE => [],
        DefinitionInterface::LISTENER_AROUND => [],
        DefinitionInterface::LISTENER_AFTER  => []
    ];
    /**
     * @var array
     */
    private $interceptedClasses;

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


    private function getPlugins($type)
    {
        $this->_loadScopedData();
        if (!isset($this->_inherited[$type]) && !array_key_exists($type, $this->_inherited)) {
            $this->_inheritPlugins($type);
        }
        return $this->_inherited[$type];
    }


    /**
     * @param $className
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getPluginsListByClass($className)
    {

        $this->getPlugins($className);

        foreach ($this->_inherited[$className] as $pluginKey => $plugin)
        {
            foreach ($this->_definitions->getMethodList($plugin['instance']) as $pluginMethod => $methodTypes) {

                if ($methodTypes & DefinitionInterface::LISTENER_AROUND) {
                    if(!array_key_exists($plugin['instance'],$this->pluginList[DefinitionInterface::LISTENER_AROUND]))
                        $this->pluginList[DefinitionInterface::LISTENER_AROUND][$plugin['instance']] = [];
                    $this->pluginList[DefinitionInterface::LISTENER_AROUND][$plugin['instance']][] = $pluginMethod ;

                }
                if ($methodTypes & DefinitionInterface::LISTENER_BEFORE) {
                    if(!array_key_exists($plugin['instance'],$this->pluginList[DefinitionInterface::LISTENER_BEFORE]))
                        $this->pluginList[DefinitionInterface::LISTENER_BEFORE][$plugin['instance']] = [];
                    $this->pluginList[DefinitionInterface::LISTENER_BEFORE][$plugin['instance']][] = $pluginMethod ;

                }
                if ($methodTypes & DefinitionInterface::LISTENER_AFTER) {
                    if(!array_key_exists($plugin['instance'],$this->pluginList[DefinitionInterface::LISTENER_AFTER]))
                        $this->pluginList[DefinitionInterface::LISTENER_AFTER][$plugin['instance']] = [];
                    $this->pluginList[DefinitionInterface::LISTENER_AFTER][$plugin['instance']][] = $pluginMethod ;
                }
            }
        }
        return $this->pluginList;
    }
}
