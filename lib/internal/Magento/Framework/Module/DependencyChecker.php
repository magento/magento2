<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Enabled module list from configuration
     *
     * @var array
     */
    private $enabledModuleList;

    /**
     * The full module list information from filesystem
     *
     * @var array
     */
    private $fullModuleList;

    /**
     * Graph
     *
     * @var Graph
     */
    private $graph;

    /**
     * @var PackageInfo
     */
    protected $packageInfo;

    /**
     * Constructor
     *
     * @param ModuleList $list
     * @param ModuleList\Loader $loader
     * @param PackageInfoFactory $packageInfoFactory
     */
    public function __construct(ModuleList $list, ModuleList\Loader $loader, PackageInfoFactory $packageInfoFactory)
    {
        $this->enabledModuleList = $list->getNames();
        $this->fullModuleList = $loader->load();
        $this->packageInfo = $packageInfoFactory->create();
    }

    /**
     * Checks dependencies when disabling modules
     *
     * @param string[] $toBeDisabledModules
     * @param string[] $currentlyEnabledModules
     * @return array
     */
    public function checkDependenciesWhenDisableModules($toBeDisabledModules, $currentlyEnabledModules = null)
    {
        $masterList = isset($currentlyEnabledModules) ? $currentlyEnabledModules: $this->enabledModuleList;
        // assume disable succeeds: currently enabled modules - to-be-disabled modules
        $enabledModules = array_diff($masterList, $toBeDisabledModules);
        return $this->checkDependencyGraph(false, $toBeDisabledModules, $enabledModules);
    }

    /**
     * Checks dependencies when enabling modules
     *
     * @param string[] $toBeEnabledModules
     * @param string[] $currentlyEnabledModules
     * @return array
     */
    public function checkDependenciesWhenEnableModules($toBeEnabledModules, $currentlyEnabledModules = null)
    {
        $masterList = isset($currentlyEnabledModules) ? $currentlyEnabledModules: $this->enabledModuleList;
        // assume enable succeeds: union of currently enabled modules and to-be-enabled modules
        $enabledModules = array_unique(array_merge($masterList, $toBeEnabledModules));
        return $this->checkDependencyGraph(true, $toBeEnabledModules, $enabledModules);
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
        $this->graph = $this->createGraph();
        $dependenciesMissingAll = [];
        $graphMode = $isEnable ? Graph::DIRECTIONAL : Graph::INVERSE;
        foreach ($moduleNames as $moduleName) {
            $dependenciesMissing = [];
            $paths = $this->graph->findPathsToReachableNodes($moduleName, $graphMode);
            $modules = array_merge(
                array_keys($this->fullModuleList),
                $this->packageInfo->getNonExistingDependencies()
            );
            foreach ($modules as $module) {
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
     * @return Graph
     */
    private function createGraph()
    {
        $nodes = [];
        $dependencies = [];

        // build the graph data
        foreach (array_keys($this->fullModuleList) as $moduleName) {
            $nodes[] = $moduleName;
            foreach ($this->packageInfo->getRequire($moduleName) as $dependModuleName) {
                if ($dependModuleName) {
                    $dependencies[] = [$moduleName, $dependModuleName];
                }
            }
        }
        $nodes = array_unique(
            array_merge($nodes, $this->packageInfo->getNonExistingDependencies())
        );

        return new Graph($nodes, $dependencies);
    }
}
