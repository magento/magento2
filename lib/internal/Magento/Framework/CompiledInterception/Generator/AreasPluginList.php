<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\App\AreaList;
use Magento\Framework\App\ObjectManager;

class AreasPluginList
{
    /**
     * @var AreaList
     */
    private $areaList;

    private $plugins;

    public function __construct(
        AreaList $areaList,
        $plugins = null
    ) {

        $this->areaList = $areaList;
        $this->plugins = $plugins;
    }

    /**
     * @return array
     */
    public function getPluginsConfigForAllAreas()
    {
        if ($this->plugins === null) {
            $this->plugins = [];
            foreach ($this->areaList->getCodes() as $scope) {
                $this->plugins[$scope] = new CompiledPluginList(ObjectManager::getInstance(), $scope);
            }
            $this->plugins['primary'] = new CompiledPluginList(ObjectManager::getInstance(), 'primary');
        }
        return $this->plugins;
    }

}
