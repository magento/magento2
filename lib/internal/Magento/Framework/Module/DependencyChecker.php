<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Data\Graph;

/**
 * Checks for dependencies between modules
 */
class DependencyChecker
{
    /**
     * Enabled module list
     *
     * @var ModuleList
     */
    private $list;

    /**
     * All module loader
     *
     * @var ModuleList\Loader
     */
    private $loader;

    /**
     * Composer package info
     *
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * Constructor
     *
     * @param ModuleList $list
     * @param ModuleList\Loader $loader
     * @param PackageInfoFactory $packageInfoFactory
     */
    public function __construct(ModuleList $list, ModuleList\Loader $loader, PackageInfoFactory $packageInfoFactory)
    {
        $this->list = $list;
        $this->loader = $loader;
        $this->packageInfo = $packageInfoFactory->create();
    }

    /**
     * Checks dependencies when disabling modules
     *
     * @param string[] $moduleNames
     * @param string $mode
     * @return array
     */
    public function checkDependenciesWhenDisableModules($moduleNames, $mode = Status::MODE_CONFIG)
    {
        if ($mode === Status::MODE_ENABLED) {
            $enabledModules = array_diff(array_keys($this->loader->load()), $moduleNames);
        } else if ($mode === Status::MODE_DISABLED) {
            return [];
        } else {
            // assume disable succeeds: currently enabled modules - to-be-disabled modules
            $enabledModules = array_diff($this->list->getNames(), $moduleNames);
        }
        return $this->checkDependencyGraph(false, $moduleNames, $enabledModules);
    }

    /**
     * Checks dependencies when enabling modules
     *
     * @param string[] $moduleNames
     * @param string $mode
     * @return array
     */
    public function checkDependenciesWhenEnableModules($moduleNames, $mode = Status::MODE_CONFIG)
    {
        if ($mode === Status::MODE_DISABLED) {
            $enabledModules = $moduleNames;
        } else if ($mode === Status::MODE_ENABLED) {
            return [];
        } else {
            // assume enable succeeds: union of currently enabled modules and to-be-enabled modules
            $enabledModules = array_unique(array_merge($this->list->getNames(), $moduleNames));
        }
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
            foreach (array_keys($this->loader->load()) as $module) {
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
        foreach (array_keys($this->loader->load()) as $moduleName) {
            $nodes[] = $moduleName;
            foreach ($packageInfo->getRequire($moduleName) as $dependModuleName) {
                if ($dependModuleName) {
                    $dependencies[] = [$moduleName, $dependModuleName];
                }
            }
        }
        $nodes = array_unique($nodes);

        return new Graph($nodes, $dependencies);
    }
}
