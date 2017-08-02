<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Generator;

use Magento\Framework\Interception;

/**
 * Provides plugin list configuration
 * @since 2.0.0
 */
class PluginList extends Interception\PluginList\PluginList
{
    /**
     * @var array
     * @since 2.0.0
     */
    private $interceptedClasses;

    /**
     * Returns plugins config
     *
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function isCurrentScope($scopeCode)
    {
        return false;
    }

    /**
     * @param array $interceptedClasses
     * @return void
     * @since 2.0.0
     */
    public function setInterceptedClasses($interceptedClasses)
    {
        $this->interceptedClasses = $interceptedClasses;
    }

    /**
     * Returns class definitions
     *
     * @return array
     * @since 2.0.0
     */
    protected function getClassDefinitions()
    {
        return $this->interceptedClasses;
    }
}
