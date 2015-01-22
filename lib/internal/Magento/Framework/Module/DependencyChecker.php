<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Data\Graph;
use Magento\Framework\Filesystem;

class DependencyChecker
{
    /**
     * Composer package info
     *
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * @param PackageInfo $packageInfo
     */
    public function __construct(PackageInfo $packageInfo)
    {
        $this->packageInfo = $packageInfo;
    }

    /**
     * Checks dependencies when disabling modules
     *
     * @param string[] $moduleNames
     * @return array
     */
    public function checkDependenciesWhenDisableModules($moduleNames)
    {
        // assume disable succeeds: currently enabled modules - to-be-disabled modules
        $enabledModules = array_diff($this->packageInfo->getEnabledModules(), $moduleNames);
        return $this->checkDependencyGraph(false, $moduleNames, $enabledModules);
    }

    /**
     * Checks dependencies when enabling modules
     *
     * @param string[] $moduleNames
     * @return array
     */
    public function checkDependenciesWhenEnableModules($moduleNames)
    {
        // assume enable succeeds: union of currently enabled modules and to-be-enabled modules
        $enabledModules = array_unique(array_merge($this->packageInfo->getEnabledModules(), $moduleNames));
        return $this->checkDependencyGraph(true, $moduleNames, $enabledModules);
    }

    /**
     * Check the dependency graph
     *
     * @param bool $isEnable
     * @param string[] $moduleNames list of modules to be enabled/disabled
     * @param string[] $enabledModules list of enabled modules assuming enable/disable succeeds
     * @return array
     */
    private function checkDependencyGraph($isEnable, $moduleNames, $enabledModules)
    {
        $dependenciesMissingAll = [];
        $graph = $this->createGraph($this->packageInfo);
        $graphMode = $isEnable ? Graph::DIRECTIONAL : Graph::INVERSE;
        foreach ($moduleNames as $moduleName) {
            $dependenciesMissing = [];
            $paths = $graph->findPathsToReachableNodes($moduleName, $graphMode);
            foreach ($this->packageInfo->getAllModuleNames() as $module) {
                if (isset($paths[$module])) {
                    if ($isEnable && !in_array($module, $enabledModules)) {
                        $dependenciesMissing[$module] = $paths[$module];
                    } else if (!$isEnable && in_array($module, $enabledModules)) {
                        $dependenciesMissing[$module] = array_reverse($paths[$module]);
                    }
                }
            }
            $dependenciesMissingAll[$moduleName] = $dependenciesMissing;
        }
        return $dependenciesMissingAll;
    }

    /**
     * Create the dependency graph
     *
     * @param PackageInfo $packageInfo
     * @return Graph
     */
    private function createGraph(PackageInfo $packageInfo)
    {
        $nodes = [];
        $dependencies = [];

        // build the graph data
        foreach ($packageInfo->getAllModuleNames() as $moduleName) {
            $nodes[] = $moduleName;
            foreach ($packageInfo->getRequire($moduleName) as $dependPackageName) {
                $depend = $packageInfo->getModuleName($dependPackageName);
                if ($depend) {
                    $dependencies[] = [$moduleName, $depend];
                }
            }
        }
        $nodes = array_unique($nodes);

        return new Graph($nodes, $dependencies);
    }
}
