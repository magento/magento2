<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Component\ComponentRegistrar;

/**
 * Provide information of dependencies and conflicts in composer.json files, mapping of package name to module name,
 * and mapping of module name to package version
 * @since 2.0.0
 */
class PackageInfo
{
    /**
     * Package name to module name map
     *
     * @var string[]
     * @since 2.0.0
     */
    private $packageModuleMap;

    /**
     * Map of module name to package version
     *
     * @var string[]
     * @since 2.0.0
     */
    private $modulePackageVersionMap;

    /**
     * "require" field of each module, contains depending modules' name
     *
     * @var array[]
     * @since 2.0.0
     */
    private $requireMap;

    /**
     * "conflict" field of each module, contains conflicting modules' name and version constraint
     *
     * @var array[]
     * @since 2.0.0
     */
    private $conflictMap;

    /**
     * Reader of composer.json files
     *
     * @var Dir\Reader
     * @since 2.0.0
     */
    private $reader;

    /**
     * @var ComponentRegistrar
     * @since 2.0.0
     */
    private $componentRegistrar;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $nonExistingDependencies = [];

    /**
     * Constructor
     *
     * @param Dir\Reader $reader
     * @param ComponentRegistrar $componentRegistrar
     * @since 2.0.0
     */
    public function __construct(Dir\Reader $reader, ComponentRegistrar $componentRegistrar)
    {
        $this->reader = $reader;
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Load the packages information
     *
     * @return void
     * @throws \Zend_Json_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    private function load()
    {
        if ($this->packageModuleMap === null) {
            $jsonData = $this->reader->getComposerJsonFiles()->toArray();
            foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
                $key = $moduleDir . '/composer.json';
                if (isset($jsonData[$key]) && $jsonData[$key]) {
                    try {
                        $packageData = \Zend_Json::decode($jsonData[$key]);
                    } catch (\Zend_Json_Exception $e) {
                        throw new \Zend_Json_Exception(
                            sprintf(
                                "%s composer.json error: %s",
                                $moduleName,
                                $e->getMessage()
                            )
                        );
                    }

                    if (isset($packageData['name'])) {
                        $this->packageModuleMap[$packageData['name']] = $moduleName;
                    }
                    if (isset($packageData['version'])) {
                        $this->modulePackageVersionMap[$moduleName] = $packageData['version'];
                    }
                    if (!empty($packageData['require'])) {
                        $this->requireMap[$moduleName] = array_keys($packageData['require']);
                    }
                    if (!empty($packageData['conflict'])) {
                        $this->conflictMap[$moduleName] = $packageData['conflict'];
                    }
                }
            }
        }
    }

    /**
     * Get module name of a package
     *
     * @param string $packageName
     * @return string
     * @since 2.0.0
     */
    public function getModuleName($packageName)
    {
        $this->load();

        $moduleName = null;
        if (isset($this->packageModuleMap[$packageName])) {
            $moduleName = $this->packageModuleMap[$packageName];
        } elseif ($this->isMagentoPackage($packageName)) {
            $moduleName = $this->convertPackageNameToModuleName($packageName);
            $this->addNonExistingDependency($moduleName);
        }

        return $moduleName;
    }

    /**
     * Add non existing dependency
     *
     * @param string $dependency
     * @return void
     * @since 2.0.0
     */
    protected function addNonExistingDependency($dependency)
    {
        if (!isset($this->nonExistingDependencies[$dependency])) {
            $this->nonExistingDependencies[$dependency] = $dependency;
        }
    }

    /**
     * Return list of non existing dependencies
     *
     * @return array
     * @since 2.0.0
     */
    public function getNonExistingDependencies()
    {
        return $this->nonExistingDependencies;
    }

    /**
     * Build module name based on internal package name
     *
     * @param string $packageName
     * @return string|null
     * @since 2.0.0
     */
    protected function convertPackageNameToModuleName($packageName)
    {
        $moduleName = str_replace('magento/module-', '', $packageName);
        $moduleName = str_replace('-', ' ', $moduleName);
        $moduleName = str_replace(' ', '', ucwords($moduleName));

        return 'Magento_' . $moduleName;
    }

    /**
     * Check if package is internal magento module
     *
     * @param string $packageName
     * @return bool
     * @since 2.0.0
     */
    protected function isMagentoPackage($packageName)
    {
        return strpos($packageName, 'magento/module-') === 0;
    }

    /**
     * Get package name of a module
     *
     * @param string $moduleName
     * @return string
     * @since 2.0.0
     */
    public function getPackageName($moduleName)
    {
        $this->load();
        return array_search($moduleName, $this->packageModuleMap) ?: '';
    }

    /**
     * Convert an array of package names to module names
     *
     * @param string[] $packageNames
     * @return string[]
     * @since 2.0.0
     */
    private function convertToModuleNames($packageNames)
    {
        $moduleNames = [];
        foreach ($packageNames as $package) {
            $moduleNames[] = $this->getModuleName($package);
        }
        return $moduleNames;
    }

    /**
     * Get all module names a module requires
     *
     * @param string $moduleName
     * @return array
     * @since 2.0.0
     */
    public function getRequire($moduleName)
    {
        $this->load();
        $require = [];
        if (isset($this->requireMap[$moduleName])) {
            $require = $this->convertToModuleNames($this->requireMap[$moduleName]);
        }
        return $require;
    }

    /**
     * Get all module names a module required by
     *
     * @param string $requiredModuleName
     * @return array
     * @since 2.2.0
     */
    public function getRequiredBy($requiredModuleName)
    {
        $this->load();
        $requiredBy = [];
        foreach ($this->requireMap as $moduleName => $moduleRequireList) {
            if (in_array($requiredModuleName, $moduleRequireList)) {
                $requiredBy[] = $moduleName;
            }
        }

        return $requiredBy;
    }

    /**
     * Get all module names a module conflicts
     *
     * @param string $moduleName
     * @return array
     * @since 2.0.0
     */
    public function getConflict($moduleName)
    {
        $this->load();
        $conflict = [];
        if (isset($this->conflictMap[$moduleName])) {
            $conflict = array_combine(
                $this->convertToModuleNames(array_keys($this->conflictMap[$moduleName])),
                $this->conflictMap[$moduleName]
            );
        }
        return $conflict;
    }

    /**
     * Get package version of a module
     *
     * @param string $moduleName
     * @return string
     * @since 2.0.0
     */
    public function getVersion($moduleName)
    {
        $this->load();
        return isset($this->modulePackageVersionMap[$moduleName]) ? $this->modulePackageVersionMap[$moduleName] : '';
    }
}
