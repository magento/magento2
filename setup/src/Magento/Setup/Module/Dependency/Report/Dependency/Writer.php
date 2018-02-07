<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Dependency;

use Magento\Setup\Module\Dependency\Report\Writer\Csv\AbstractWriter;

/**
 * Csv file writer for modules dependencies report
 */
class Writer extends AbstractWriter
{
    /**
     * Template method. Prepare data step
     *
     * @param \Magento\Setup\Module\Dependency\Report\Dependency\Data\Config $config
     * @return array
     */
    protected function prepareData($config)
    {
        $data[] = ['', 'All', 'Hard', 'Soft'];
        $data[] = [
            'Total number of dependencies',
            $config->getDependenciesCount(),
            $config->getHardDependenciesCount(),
            $config->getSoftDependenciesCount(),
        ];
        $data[] = [];

        if ($config->getDependenciesCount()) {
            $data[] = ['Dependencies for each module:', 'All', 'Hard', 'Soft'];
            foreach ($config->getModules() as $module) {
                if ($module->getDependenciesCount()) {
                    $data[] = [
                        $module->getName(),
                        $module->getDependenciesCount(),
                        $module->getHardDependenciesCount(),
                        $module->getSoftDependenciesCount(),
                    ];
                    foreach ($module->getDependencies() as $dependency) {
                        $data[] = [
                            ' -- ' . $dependency->getModule(),
                            '',
                            (int)$dependency->isHard(),
                            (int)(!$dependency->isHard()),
                        ];
                    }
                    $data[] = [];
                }
            }
        }
        array_pop($data);

        return $data;
    }
}
