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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\App\Module\Declaration\Reader;

class Filesystem extends \Magento\Config\Reader\Filesystem
{
    /**
     * The list of allowed modules
     *
     * @var array
     */
    protected $_allowedModules;

    /**
     * {@inheritdoc}
     */
    protected $_idAttributes = array(
        '/config/module' => 'name',
        '/config/module/depends/extension' => 'name',
        '/config/module/depends/choice/extension' => 'name',
        '/config/module/sequence/module' => 'name',
    );

    /**
     * @param \Magento\App\Module\Declaration\FileResolver $fileResolver
     * @param \Magento\App\Module\Declaration\Converter\Dom $converter
     * @param \Magento\App\Module\Declaration\SchemaLocator $schemaLocator
     * @param \Magento\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param array $allowedModules
     */
    public function __construct(
        \Magento\App\Module\Declaration\FileResolver $fileResolver,
        \Magento\App\Module\Declaration\Converter\Dom $converter,
        \Magento\App\Module\Declaration\SchemaLocator $schemaLocator,
        \Magento\Config\ValidationStateInterface $validationState,
        $fileName = 'module.xml',
        $idAttributes = array(),
        $domDocumentClass = 'Magento\Config\Dom',
        array $allowedModules = array()
    ) {
        parent::__construct(
            $fileResolver, $converter, $schemaLocator, $validationState, $fileName, $idAttributes, $domDocumentClass
        );
        $this->_allowedModules = $allowedModules;
    }

    /**
     * {@inheritdoc}
     */
    public function read($scope = null)
    {
        $activeModules = $this->_filterActiveModules(parent::read($scope));
        foreach ($activeModules as $moduleConfig) {
            $this->_checkModuleDependencies($moduleConfig, $activeModules);
        }
        return $this->_sortModules($activeModules);
    }

    /**
     * Retrieve declarations of active modules
     *
     * @param array $modules
     * @return array
     */
    protected function _filterActiveModules(array $modules)
    {
        $activeModules = array();
        foreach ($modules as $moduleName => $moduleConfig) {
            if ($moduleConfig['active']
                && (empty($this->_allowedModules) || in_array($moduleConfig['name'], $this->_allowedModules))
            ) {
                $activeModules[$moduleName] = $moduleConfig;
            }
        }
        return $activeModules;
    }

    /**
     * Check dependencies of the given module
     *
     * @param array $moduleConfig
     * @param array $activeModules
     * @throws \Exception
     */
    protected function _checkModuleDependencies(array $moduleConfig, array $activeModules)
    {
        // Check that required modules are active
        foreach ($moduleConfig['dependencies']['modules'] as $moduleName) {
            if (!isset($activeModules[$moduleName])) {
                throw new \Exception(
                    "Module '{$moduleConfig['name']}' depends on '{$moduleName}' that is missing or not active."
                );
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
     * Check if at least one of the extensions is loaded
     *
     * @param string $moduleName
     * @param array $altExtensions
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
                "Module '{$moduleName}' depends on at least one of the following PHP extensions: "
                    . implode(',', $extensionNames) . '.'
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

    /**
     * Sort module declarations based on module dependencies
     *
     * @param array $modules
     * @return array
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    protected function _sortModules(array $modules)
    {
        /**
         * The following map is needed only for sorting
         * (in order not to add extra information about dependencies to module config)
         */
        $moduleDependencyMap = array();
        foreach (array_keys($modules) as $moduleName) {
            $moduleDependencyMap[] = array(
                'moduleName' => $moduleName,
                'dependencies' => $this->_getExtendedModuleDependencies($moduleName, $modules),
            );
        }

        // Use "bubble sorting" because usort does not check each pair of elements and in this case it is important
        $modulesCount = count($moduleDependencyMap);
        for ($i = 0; $i < $modulesCount - 1; $i++) {
            for ($j = $i; $j < $modulesCount; $j++) {
                if (in_array($moduleDependencyMap[$j]['moduleName'], $moduleDependencyMap[$i]['dependencies'])) {
                    $temp = $moduleDependencyMap[$i];
                    $moduleDependencyMap[$i] = $moduleDependencyMap[$j];
                    $moduleDependencyMap[$j] = $temp;
                }
            }
        }

        $sortedModules = array();
        foreach ($moduleDependencyMap as $moduleDependencyPair) {
            $sortedModules[$moduleDependencyPair['moduleName']] = $modules[$moduleDependencyPair['moduleName']];
        }

        return $sortedModules;
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
    protected function _getExtendedModuleDependencies($moduleName,  array $modules, array $usedModules = array())
    {
        $usedModules[] = $moduleName;
        $dependencyList = $modules[$moduleName]['dependencies']['modules'];
        foreach ($dependencyList as $relatedModuleName) {
            if (in_array($relatedModuleName, $usedModules)) {
                throw new \Exception(
                    "Module '$moduleName' cannot depend on '$relatedModuleName' since it creates circular dependency."
                );
            }
            if (empty($modules[$relatedModuleName])) {
                continue;
            }
            $relatedDependencies = $this->_getExtendedModuleDependencies($relatedModuleName, $modules, $usedModules);
            $dependencyList = array_unique(array_merge($dependencyList, $relatedDependencies));
        }
        return $dependencyList;
    }
}
