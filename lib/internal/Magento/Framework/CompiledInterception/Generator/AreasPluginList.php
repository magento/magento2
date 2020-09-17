<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\Config\Scope;

/**
 * Plugin list provider for area.
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
                if ($defaultScopePluginList === null) {
                    $defaultScopePluginList = $this->compiledPluginListFactory->create();
                    $defaultScopePluginList->setScope($configScope);
                    $defaultScopePluginList->getNext('dummy', 'dummy');
                    $defaultScope = $scope;
                } else {
                    $this->plugins[$scope] = clone $defaultScopePluginList;
                    $this->plugins[$scope]->setScope($configScope);
                }
            }
            $this->plugins[$defaultScope] = $defaultScopePluginList;
        }

        return $this->plugins;
    }
}
