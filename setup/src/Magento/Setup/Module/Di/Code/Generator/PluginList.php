<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Code\Generator;

use Magento\Framework\Interception;

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
