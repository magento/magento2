<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Dependency;

use Magento\Setup\Module\Dependency\Report\Builder\AbstractBuilder;

/**
 *  Modules dependencies report builder
 * @since 2.0.0
 */
class Builder extends AbstractBuilder
{
    /**
     * Template method. Prepare data for writer step
     *
     * @param array $modulesData
     * @return \Magento\Setup\Module\Dependency\Report\Dependency\Data\Config
     * @since 2.0.0
     */
    protected function buildData($modulesData)
    {
        $modules = [];
        foreach ($modulesData as $moduleData) {
            $dependencies = [];
            foreach ($moduleData['dependencies'] as $dependencyData) {
                $dependencies[] = new Data\Dependency($dependencyData['module'], $dependencyData['type']);
            }
            $modules[] = new Data\Module($moduleData['name'], $dependencies);
        }
        return new Data\Config($modules);
    }
}
