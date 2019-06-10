<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\App\AreaList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\Scope;

class AreasPluginList
{
    /**
     * @var Scope
     */
    private $scope;

    private $plugins;

    /**
     * AreasPluginList constructor.
     * @param Scope $scope
     * @param null $plugins
     */
    public function __construct(
        Scope $scope,
        $plugins = null
    ) {
        $this->scope = $scope;
        $this->plugins = $plugins;
    }

    /**
     * @return array
     */
    public function getPluginsConfigForAllAreas()
    {
        if ($this->plugins === null) {
            $this->plugins = [];
            //this is to emulate order M2 is reading scopes config to use scope cache
            //"global|primary" should be loaded first and then "global|primary|frontend" etc.
            $core = new CompiledPluginList(ObjectManager::getInstance(), new StaticScope('primary'));
            $core->getNext('dummy', 'dummy');
            $this->plugins['primary'] = $core;
            foreach ($this->scope->getAllScopes() as $scope) {
                $this->plugins[$scope] = clone $core;
                $this->plugins[$scope]->setScope(new StaticScope($scope));
                $this->plugins[$scope]->getNext('dummy', 'dummy');
            }
        }
        return $this->plugins;
    }

}
