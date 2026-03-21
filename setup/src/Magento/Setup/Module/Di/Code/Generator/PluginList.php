<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Module\Di\Code\Generator;

use Magento\Framework\App\Area;
use Magento\Framework\Interception;

/**
 * Provides plugin list configuration
 */
class PluginList extends Interception\PluginList\PluginList
{
    /**
     * @var array
     */
    private $interceptedClasses;

    /**
     * Resets accumulated per-area state so this instance can be reused without cloning.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->_data = [];
        $this->_inherited = [];
        $this->_processed = null;
        $this->_pluginInstances = [];
        $this->_scopePriorityScheme = [Area::AREA_GLOBAL];
    }

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
     * @param array $interceptedClasses
     * @return void
     */
    public function setInterceptedClasses($interceptedClasses)
    {
        $this->interceptedClasses = $interceptedClasses;
    }

    /**
     * Returns class definitions
     *
     * @return array
     */
    protected function getClassDefinitions()
    {
        return $this->interceptedClasses;
    }
}
