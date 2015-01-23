<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

/**
 * Provide information of dependencies and conflicts in composer.json files and mapping of package name to module name
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
     * "require" field of each module
     *
     * @var array[]
     */
    private $requireMap;

    /**
     * "conflict" field of each module
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
     * Constructor
     *
     * @param ModuleList\Loader $loader
     * @param Dir\Reader $reader
     */
    public function __construct(ModuleList\Loader $loader, Dir\Reader $reader)
    {
        $this->loader = $loader;
        $this->reader = $reader;
    }

    /**
     * Load the packages information
     *
     * @return void
     */
    private function load()
    {
        if ($this->packageModuleMap === null) {
            /**
             * array keys: module name in module.xml; array values: raw content from composer.json
             * this raw data is used to create a dependency graph and also a package name-module name mapping
             */
            $rawData = array_combine(array_keys($this->loader->load()), $this->reader->getComposerJsonFiles()->toArray());
            foreach ($rawData as $moduleName => $jsonData) {
                $jsonData = \Zend_Json::decode($jsonData);
                $this->packageModuleMap[$jsonData['name']] = $moduleName;
                if (!empty($jsonData['require'])) {
                    $this->requireMap[$moduleName] = array_keys($jsonData['require']);
                }
                if (!empty($jsonData['conflict'])) {
                    $this->conflictMap[$moduleName] = array_keys($jsonData['conflict']);
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
     * Get all package names a module requires
     *
     * @param string $moduleName
     * @param bool $returnModuleName
     * @return array
     */
    public function getRequire($moduleName, $returnModuleName = true)
    {
        $this->load();
        $require = [];
        if (isset($this->requireMap[$moduleName])) {
            if ($returnModuleName) {
                $require = $this->convertToModuleNames($this->requireMap[$moduleName]);
            } else {
                $require = $this->requireMap[$moduleName];
            }
        }
        return $require;
    }

    /**
     * Get all package names a module conflicts
     *
     * @param string $moduleName
     * @param bool $returnModuleName
     * @return array
     */
    public function getConflict($moduleName, $returnModuleName = true)
    {
        $this->load();
        $conflict = [];
        if (isset($this->conflictMap[$moduleName])) {
            if ($returnModuleName) {
                $conflict = $this->convertToModuleNames($this->conflictMap[$moduleName]);
            } else {
                $conflict = $this->conflictMap[$moduleName];
            }
        }
        return $conflict;
    }
}
