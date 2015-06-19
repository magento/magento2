<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Stdlib\String;

/**
 * Provide information of dependencies and conflicts in composer.json files, mapping of package name to module name,
 * and mapping of module name to package version
 */
class PackageInfo
{
    /**
     * Package name to module name map
     *
     * @var string[]
     */
    private $packageModuleMap;

    /**
     * Map of module name to package version
     *
     * @var string[]
     */
    private $modulePackageVersionMap;

    /**
     * "require" field of each module, contains depending modules' name
     *
     * @var array[]
     */
    private $requireMap;

    /**
     * "conflict" field of each module, contains conflicting modules' name and version constraint
     *
     * @var array[]
     */
    private $conflictMap;

    /**
     * All modules loader
     *
     * @var ModuleList\Loader
     */
    private $loader;

    /**
     * Reader of composer.json files
     *
     * @var Dir\Reader
     */
    private $reader;

    /**
     * String utilities
     *
     * @var String
     */
    private $string;

    /**
     * Constructor
     *
     * @param ModuleList\Loader $loader
     * @param Dir\Reader $reader
     * @param \Magento\Framework\Stdlib\String $string
     */
    public function __construct(ModuleList\Loader $loader, Dir\Reader $reader, String $string)
    {
        $this->loader = $loader;
        $this->reader = $reader;
        $this->string = $string;
    }

    /**
     * Load the packages information
     *
     * @return void
     */
    private function load()
    {
        if ($this->packageModuleMap === null) {
            $jsonData = $this->reader->getComposerJsonFiles()->toArray();
            foreach (array_keys($this->loader->load()) as $moduleName) {
                $key = $this->string->upperCaseWords($moduleName, '_', '/') . '/composer.json';
                if (isset($jsonData[$key])) {
                    $packageData = \Zend_Json::decode($jsonData[$key]);
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
     */
    public function getModuleName($packageName)
    {
        $this->load();
        return isset($this->packageModuleMap[$packageName]) ? $this->packageModuleMap[$packageName] : '';
    }

    /**
     * Get package name of a module
     *
     * @param string $moduleName
     * @return string
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
     * Get all module names a module conflicts
     *
     * @param string $moduleName
     * @return array
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
     */
    public function getVersion($moduleName)
    {
        $this->load();
        return isset($this->modulePackageVersionMap[$moduleName]) ? $this->modulePackageVersionMap[$moduleName] : '';
    }
}
