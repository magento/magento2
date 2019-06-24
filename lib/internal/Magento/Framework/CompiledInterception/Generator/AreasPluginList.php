<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\Scope;

/**
 * Class AreasPluginList
 */
class AreasPluginList
{
    /** @var Scope */
    private $scope;

    /** @var array */
    private $plugins;

    /**
     * AreasPluginList constructor.
     * @param Scope $scope
     * @param array|null $plugins
     */
    public function __construct(
        Scope $scope,
        $plugins = null
    ) {
        $this->scope = $scope;
        $this->plugins = $plugins;
    }

    /**
     * Get array of plugns config indexed by scope code
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
                if ($defaultScopePluginList === null) {
                    $defaultScopePluginList = new CompiledPluginList(
                        ObjectManager::getInstance(),
                        new StaticScope($scope)
                    );
                    $defaultScopePluginList->getNext('dummy', 'dummy');
                    $defaultScope = $scope;
                } else {
                    $this->plugins[$scope] = clone $defaultScopePluginList;
                    $this->plugins[$scope]->setScope(new StaticScope($scope));
                    //$this->plugins[$scope]->getNext('dummy', 'dummy');
                }
            }
            $this->plugins[$defaultScope] = $defaultScopePluginList;
        }
        return $this->plugins;
    }
}
