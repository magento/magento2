<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Dependency\Report\Framework;

use Magento\Tools\Dependency\Report\Writer\Csv\AbstractWriter;

/**
 * Csv file writer for framework dependencies report
 */
class Writer extends AbstractWriter
{
    /**
     * Template method. Prepare data step
     *
     * @param \Magento\Tools\Dependency\Report\Framework\Data\Config $config
     * @return array
     */
    protected function prepareData($config)
    {
        $data[] = ['Dependencies of framework:', 'Total number'];
        $data[] = ['', $config->getDependenciesCount()];
        $data[] = [];

        if ($config->getDependenciesCount()) {
            $data[] = ['Dependencies for each module:', ''];
            foreach ($config->getModules() as $module) {
                $data[] = [$module->getName(), $module->getDependenciesCount()];
                foreach ($module->getDependencies() as $dependency) {
                    $data[] = [' -- ' . $dependency->getLib(), $dependency->getCount()];
                }
                $data[] = [];
            }
        }
        array_pop($data);

        return $data;
    }
}
