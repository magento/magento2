<?php
/**
 * Dependency manager, checks if all dependencies on modules and extensions are satisfied
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Setup\Module\Dependency;

interface ManagerInterface
{
    /**
     * Check dependencies of the given module
     *
     * @param array $moduleConfig
     * @param array $activeModules
     * @return void
     * @throws \Exception
     */
    public function checkModuleDependencies(array $moduleConfig, array $activeModules = array());

    /**
     * Recursively identify all module dependencies and detect circular ones
     *
     * @param string $moduleName
     * @param array $modules
     * @param array $usedModules
     * @return array
     * @throws \Exception
     */
    public function getExtendedModuleDependencies($moduleName, array $modules, array $usedModules = array());
}
