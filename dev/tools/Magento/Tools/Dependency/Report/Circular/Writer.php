<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
