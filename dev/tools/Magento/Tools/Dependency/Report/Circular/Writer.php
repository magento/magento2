<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Dependency\Report\Circular;

use Magento\Tools\Dependency\Report\Writer\Csv\AbstractWriter;

/**
 * Csv file writer for circular dependencies report
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
     * @param \Magento\Tools\Dependency\Report\Circular\Data\Config $config
     * @return array
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
