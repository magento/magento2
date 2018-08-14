<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Framework\Data;

use Magento\Setup\Module\Dependency\Report\Data\Config\AbstractConfig;

/**
 * Config
 *
 * @method \Magento\Setup\Module\Dependency\Report\Framework\Data\Module[] getModules()
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
            $dependenciesCount += $module->getDependenciesCount();
        }
        return $dependenciesCount;
    }
}
