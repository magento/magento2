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
namespace Magento\Tools\Dependency\Report\Dependency;

use Magento\Tools\Dependency\Report\Writer\Csv\AbstractWriter;

/**
 * Csv file writer for modules dependencies report
 */
class Writer extends AbstractWriter
{
    /**
     * Template method. Prepare data step
     *
     * @param \Magento\Tools\Dependency\Report\Dependency\Data\Config $config
     * @return array
     */
    protected function prepareData($config)
    {
        $data[] = array('', 'All', 'Hard', 'Soft');
        $data[] = array(
            'Total number of dependencies',
            $config->getDependenciesCount(),
            $config->getHardDependenciesCount(),
            $config->getSoftDependenciesCount()
        );
        $data[] = array();

        if ($config->getDependenciesCount()) {
            $data[] = array('Dependencies for each module:', 'All', 'Hard', 'Soft');
            foreach ($config->getModules() as $module) {
                if ($module->getDependenciesCount()) {
                    $data[] = array(
                        $module->getName(),
                        $module->getDependenciesCount(),
                        $module->getHardDependenciesCount(),
                        $module->getSoftDependenciesCount()
                    );
                    foreach ($module->getDependencies() as $dependency) {
                        $data[] = array(
                            ' -- ' . $dependency->getModule(),
                            '',
                            (int)$dependency->isHard(),
                            (int)(!$dependency->isHard())
                        );
                    }
                    $data[] = array();
                }
            }
        }
        array_pop($data);

        return $data;
    }
}
