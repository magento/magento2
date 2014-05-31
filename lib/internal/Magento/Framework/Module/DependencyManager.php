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
namespace Magento\Framework\Module;

class DependencyManager implements DependencyManagerInterface
{
    /**
     * Check dependencies of the given module
     *
     * @param array $moduleConfig
     * @param array $activeModules
     * @return void
     * @throws \Exception
     */
    public function checkModuleDependencies(array $moduleConfig, array $activeModules = array())
    {
        // Check that required modules are active
        if ($activeModules) {
            foreach ($moduleConfig['dependencies']['modules'] as $moduleName) {
                if (!isset($activeModules[$moduleName])) {
                    throw new \Exception(
                        "Module '{$moduleConfig['name']}' depends on '{$moduleName}' that is missing or not active."
                    );
                }
            }
        }

        // Check that required extensions are loaded
        foreach ($moduleConfig['dependencies']['extensions']['strict'] as $extensionData) {
            $extensionName = $extensionData['name'];
            $minVersion = isset($extensionData['minVersion']) ? $extensionData['minVersion'] : null;
            if (!$this->_isPhpExtensionLoaded($extensionName, $minVersion)) {
                throw new \Exception(
                    "Module '{$moduleConfig['name']}' depends on '{$extensionName}' PHP extension that is not loaded."
                );
            }
        }
        foreach ($moduleConfig['dependencies']['extensions']['alternatives'] as $altExtensions) {
            $this->_checkAlternativeExtensions($moduleConfig['name'], $altExtensions);
        }
    }

    /**
     * Recursively identify all module dependencies and detect circular ones
     *
     * @param string $moduleName
     * @param array $modules
     * @param array $usedModules
     * @return array
     * @throws \Exception
     */
    public function getExtendedModuleDependencies($moduleName, array $modules, array $usedModules = array())
    {
        $usedModules[] = $moduleName;
        $dependencyList = $modules[$moduleName]['dependencies']['modules'];
        foreach ($dependencyList as $relatedModuleName) {
            if (in_array($relatedModuleName, $usedModules)) {
                throw new \Exception(
                    "Module '{$moduleName}' cannot depend on '{$relatedModuleName}' since it creates circular dependency."
                );
            }
            if (empty($modules[$relatedModuleName])) {
                continue;
            }
            $relatedDependencies = $this->getExtendedModuleDependencies($relatedModuleName, $modules, $usedModules);
            $dependencyList = array_unique(array_merge($dependencyList, $relatedDependencies));
        }
        return $dependencyList;
    }

    /**
     * Check if at least one of the extensions is loaded
     *
     * @param string $moduleName
     * @param array $altExtensions
     * @return void
     * @throws \Exception
     */
    protected function _checkAlternativeExtensions($moduleName, array $altExtensions)
    {
        $extensionNames = array();
        foreach ($altExtensions as $extensionData) {
            $extensionName = $extensionData['name'];
            $minVersion = isset($extensionData['minVersion']) ? $extensionData['minVersion'] : null;
            if ($this->_isPhpExtensionLoaded($extensionName, $minVersion)) {
                return;
            }
            $extensionNames[] = $extensionName;
        }
        if (!empty($extensionNames)) {
            throw new \Exception(
                "Module '{$moduleName}' depends on at least one of the following PHP extensions: " . implode(
                    ',',
                    $extensionNames
                ) . '.'
            );
        }
        return;
    }

    /**
     * Check if required version of PHP extension is loaded
     *
     * @param string $extensionName
     * @param string|null $minVersion
     * @return boolean
     */
    protected function _isPhpExtensionLoaded($extensionName, $minVersion = null)
    {
        if (extension_loaded($extensionName)) {
            if (is_null($minVersion)) {
                return true;
            } elseif (version_compare($minVersion, phpversion($extensionName), '<=')) {
                return true;
            }
        }
        return false;
    }
}
