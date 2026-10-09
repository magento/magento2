<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Module\Di\Code\Generator;

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
     * Returns raw merged plugin declarations for the loaded scopes.
     *
     * Unlike getPluginsConfig(), this includes plugins declared on types that
     * do not exist and have not been inherited/filtered.
     *
     * @return array
     */
    public function getConfiguredPluginData(): array
    {
        $this->_loadScopedData();

        return $this->_data;
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
     * Sets intercepted classes used when inheriting plugin configuration.
     *
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
