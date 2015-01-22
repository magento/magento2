<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

class PackageInfo
{
    /**
     * Package name to module name map
     *
     * @var string[]
     */
    private $packageNameMap;

    /**
     * Module name to package name map
     *
     * @var string[]
     */
    private $moduleNameMap;

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
     * List of enabled modules
     *
     * @var string[]
     */
    private $enabledModules;

    /**
     * @param ModuleList $list
     * @param ModuleList\Loader $loader
     * @param Dir\Reader $reader
     */
    public function __construct(ModuleList $list, ModuleList\Loader $loader, Dir\Reader $reader)
    {
        $this->enabledModules = $list->getNames();
        /**
         * array keys: module name in module.xml; array values: raw content from composer.json
         * this raw data is used to create a dependency graph and also a package name-module name mapping
         */
        $rawData = array_combine(array_keys($loader->load()), $reader->getComposerJsonFiles()->toArray());
        foreach ($rawData as $moduleName => $jsonData) {
            $jsonData = \Zend_Json::decode($jsonData);
            $this->packageNameMap[$moduleName] = $jsonData['name'];
            $this->moduleNameMap[$jsonData['name']] = $moduleName;
            if (!empty($jsonData['require'])) {
                $this->requireMap[$moduleName] = array_keys($jsonData['require']);
            }
            if (!empty($jsonData['conflict'])) {
                $this->conflictMap[$moduleName] = array_keys($jsonData['conflict']);
            }
        }
    }

    /**
     * Get all modules names
     *
     * @return array
     */
    public function getAllModuleNames()
    {
        return array_values($this->moduleNameMap);
    }

    /**
     * Get only enabled modules
     *
     * @return string[]
     */
    public function getEnabledModules()
    {
        return $this->enabledModules;
    }

    /**
     * Get package name of a module
     *
     * @param string $moduleName
     * @return string
     */
    public function getPackageName($moduleName)
    {
        return isset($this->packageNameMap[$moduleName]) ? $this->packageNameMap[$moduleName] : '';
    }

    /**
     * Get module name of a package
     *
     * @param string $packageName
     * @return string
     */
    public function getModuleName($packageName)
    {
        return isset($this->moduleNameMap[$packageName]) ? $this->moduleNameMap[$packageName] : '';
    }

    /**
     * Get all package names a module requires
     *
     * @param string $moduleName
     * @return array
     */
    public function getRequire($moduleName)
    {
        return isset($this->requireMap[$moduleName]) ? $this->requireMap[$moduleName] : [];
    }

    /**
     * Get all package names a module conflicts
     *
     * @param string $moduleName
     * @return array
     */
    public function getConflict($moduleName)
    {
        return isset($this->conflictMap[$moduleName]) ? $this->conflictMap[$moduleName] : [];
    }
}
