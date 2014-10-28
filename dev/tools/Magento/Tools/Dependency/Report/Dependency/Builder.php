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

use Magento\Tools\Dependency\Report\Builder\AbstractBuilder;

/**
 *  Modules dependencies report builder
 */
class Builder extends AbstractBuilder
{
    /**
     * Template method. Prepare data for writer step
     *
     * @param array $modulesData
     * @return \Magento\Tools\Dependency\Report\Dependency\Data\Config
     */
    protected function buildData($modulesData)
    {
        $modules = array();
        foreach ($modulesData as $moduleData) {
            $dependencies = array();
            foreach ($moduleData['dependencies'] as $dependencyData) {
                $dependencies[] = new Data\Dependency($dependencyData['module'], $dependencyData['type']);
            }
            $modules[] = new Data\Module($moduleData['name'], $dependencies);
        }
        return new Data\Config($modules);
    }
}
