<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Filesystem;

class DependencyChecker extends Checker
{
    /**
     * @var DependencyGraphFactory
     */
    protected $factory;

    /**
     * @param DependencyGraphFactory $factory
     * @param Mapper $mapper
     */
    public function __construct(DependencyGraphFactory $factory, Mapper $mapper)
    {
        parent::__construct($mapper);
        $this->factory = $factory;
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
        $this->enabledModules = array_diff($this->enabledModules, $moduleNames);
        $dependenciesMissingAll = [];
        $graph = $this->factory->create($this->mapper, $this->modulesData);
        foreach ($moduleNames as $moduleName) {
            $dependenciesMissing = [];
            $graph->traverseGraph($moduleName, DependencyGraph::INVERSE);
            foreach (array_keys($this->modulesData) as $module) {
                $dependencyChain = $graph->getChain($module);
                if (!empty($dependencyChain) && $this->checkIfEnabled($module)) {
                    $dependenciesMissing[$module] = array_reverse($dependencyChain);
                }
            }
            $dependenciesMissingAll[$moduleName] = $dependenciesMissing;
        }
        return $dependenciesMissingAll;
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
        $this->enabledModules = array_unique(array_merge($this->enabledModules, $moduleNames));
        $dependenciesMissingAll = [];
        $graph = $this->factory->create($this->mapper, $this->modulesData);
        foreach ($moduleNames as $moduleName) {
            $dependenciesMissing = [];
            $graph->traverseGraph($moduleName);
            foreach (array_keys($this->modulesData) as $module) {
                $dependencyChain = $graph->getChain($module);
                if (!empty($dependencyChain) && !$this->checkIfEnabled($module)) {
                    foreach ($dependencyChain as $key => $node) {
                        if (!$this->checkIfEnabled($node)) {
                            $dependencyChain[$key] = $dependencyChain[$key] . '(disabled)';
                        }
                    }
                    $dependenciesMissing[$module] = $dependencyChain;
                }
            }
            $dependenciesMissingAll[$moduleName] = $dependenciesMissing;
        }
        return $dependenciesMissingAll;
    }


    /**
     * Check if module is enabled
     *
     * @param string $moduleName
     * @return bool
     */
    protected function checkIfEnabled($moduleName)
    {
        return array_search($moduleName, $this->enabledModules) !== false;
    }
}
