<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\Scope;
use Magento\Framework\Config\ScopeInterfaceFactory;

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
     * @var ScopeInterfaceFactory
     */
    private $scopeInterfaceFactory;

    /**
     * @var CompiledPluginListFactory
     */
    private $compiledPluginListFactory;

    /**
     * @var array
     */
    private $plugins;

    /**
     * AreasPluginList constructor.
     * @param Scope $scope
     * @param ScopeInterfaceFactory $scopeInterfaceFactory
     * @param CompiledPluginListFactory $compiledPluginListFactory
     * @param array|null $plugins
     */
    public function __construct(
        Scope $scope,
        ScopeInterfaceFactory $scopeInterfaceFactory,
        CompiledPluginListFactory $compiledPluginListFactory,
        ?array $plugins = null
    ) {
        $this->scope = $scope;
        $this->scopeInterfaceFactory = $scopeInterfaceFactory;
        $this->plugins = $plugins;
        $this->compiledPluginListFactory = $compiledPluginListFactory;
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
            $objectManager = ObjectManager::getInstance();
            foreach ($this->scope->getAllScopes() as $scope) {
                $configScope = $this->scopeInterfaceFactory->create(
                    [
                        'scope' => $scope,
                    ]
                );
                if ($defaultScopePluginList === null) {
                    $defaultScopePluginList = $this->compiledPluginListFactory->create(
                        [
                            'objectManager' => $objectManager,
                            'scope' => $configScope
                        ]
                    );
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
