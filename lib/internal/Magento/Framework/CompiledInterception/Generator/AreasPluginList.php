<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\Config\Scope;

/**
 * Class AreasPluginList
 */
class AreasPluginList
{
    /**
     * @var Scope
     */
    private $scope;

    /**
     * @var StaticScopeFactory
     */
    private $staticScopeFactory;

    /**
     * @var CompiledPluginListFactory
     */
    private $compiledPluginListFactory;

    /**
     * @var array
     */
    private $plugins;

    /**
     * @param Scope $scope
     * @param StaticScopeFactory $staticScopeFactory
     * @param CompiledPluginListFactory $compiledPluginListFactory
     * @param array|null $plugins
     */
    public function __construct(
        Scope $scope,
        StaticScopeFactory $staticScopeFactory,
        CompiledPluginListFactory $compiledPluginListFactory,
        ?array $plugins = null
    ) {
        $this->scope = $scope;
        $this->staticScopeFactory = $staticScopeFactory;
        $this->compiledPluginListFactory = $compiledPluginListFactory;
        $this->plugins = $plugins;
    }

    /**
     * Get array of plugins config indexed by scope code
     *
     * @return array
     */
    public function getPluginsConfigForAllAreas()
    {
        if ($this->plugins === null) {
            $this->plugins = [];
            //this is to emulate order M2 is reading scopes config to use scope cache
            //"global|primary" should be loaded first and then "global|primary|frontend" etc.
            $defaultScopePluginList = $defaultScope = null;
            foreach ($this->scope->getAllScopes() as $scope) {
                $configScope = $this->staticScopeFactory->create(
                    [
                        'scope' => $scope,
                    ]
                );
                $pluginList = $this->prepareCompiledPluginList($configScope);
                if ($defaultScopePluginList === null) {
                    $defaultScopePluginList = $pluginList;
                    $defaultScope = $scope;
                } else {
                    $this->plugins[$scope] = $pluginList;
                }
            }
            $this->plugins[$defaultScope] = $defaultScopePluginList;
        }

        return $this->plugins;
    }

    /**
     * Create CompiledPluginList and prepare it for use.
     *
     * @param StaticScope $configScope
     * @return CompiledPluginList
     */
    private function prepareCompiledPluginList(StaticScope $configScope): CompiledPluginList
    {
        $pluginList = $this->compiledPluginListFactory->create();
        $pluginList->setScope($configScope);
        $pluginList->getNext('dummy', 'dummy');

        return $pluginList;
    }
}
