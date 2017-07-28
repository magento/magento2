<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Circular;

use Magento\Setup\Module\Dependency\Report\Writer\Csv\AbstractWriter;

/**
 * Csv file writer for circular dependencies report
 * @since 2.0.0
 */
class Writer extends AbstractWriter
{
    /**
     * Modules chain separator
     */
    const MODULES_SEPARATOR = '->';

    /**
     * Template method. Prepare data step
     *
     * @param \Magento\Setup\Module\Dependency\Report\Circular\Data\Config $config
     * @return array
     * @since 2.0.0
     */
    protected function prepareData($config)
    {
        $data[] = ['Circular dependencies:', 'Total number of chains'];
        $data[] = ['', $config->getDependenciesCount()];
        $data[] = [];

        if ($config->getDependenciesCount()) {
            $data[] = ['Circular dependencies for each module:', ''];
            foreach ($config->getModules() as $module) {
                $data[] = [$module->getName(), $module->getChainsCount()];
                foreach ($module->getChains() as $chain) {
                    $data[] = [implode(self::MODULES_SEPARATOR, $chain->getModules())];
                }
                $data[] = [];
            }
        }
        array_pop($data);

        return $data;
    }
}
