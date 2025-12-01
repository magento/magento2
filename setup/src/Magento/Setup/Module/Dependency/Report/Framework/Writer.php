<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Module\Dependency\Report\Framework;

use Magento\Setup\Module\Dependency\Report\Writer\Csv\AbstractWriter;

/**
 * Csv file writer for framework dependencies report
 */
class Writer extends AbstractWriter
{
    /**
     * Template method. Prepare data step
     *
     * @param \Magento\Setup\Module\Dependency\Report\Framework\Data\Config $config
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
