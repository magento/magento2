<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Dependency\Report\Circular\Data;

use Magento\Tools\Dependency\Report\Data\Config\AbstractConfig;

/**
 * Config
 *
 * @method \Magento\Tools\Dependency\Report\Circular\Data\Module[] getModules()
 */
class Config extends AbstractConfig
{
    /**
     * {@inheritdoc}
     */
    public function getDependenciesCount()
    {
        $dependenciesCount = 0;
        foreach ($this->getModules() as $module) {
            $dependenciesCount += $module->getChainsCount();
        }
        return $dependenciesCount;
    }
}
