<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Circular\Data;

use Magento\Setup\Module\Dependency\Report\Data\Config\AbstractConfig;

/**
 * Config
 *
 * @method \Magento\Setup\Module\Dependency\Report\Circular\Data\Module[] getModules()
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
