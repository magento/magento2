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
     * Checks modules that are depending on the to-be-disabled module
     *
     * @param string $moduleName
     * @return array
     */
    public function checkDependencyWhenDisableModule($moduleName)
    {
        $dependenciesMissing = [];
        $graph = $this->factory->create($this->modules);
        $graph->traverseGraph($moduleName, DependencyGraph::INVERSE);
        foreach ($this->modules as $module) {
            $dependencyChain = $graph->getChain($module);
            if (!empty($dependencyChain) && $this->checkIfEnabled($module)) {
                $dependenciesMissing[$module] = array_reverse($dependencyChain);
            }
        }
        return $dependenciesMissing;
    }

    /**
     * Checks modules the to-be-enabled module is depending on
     *
     * @param string $moduleName
     * @return array
     */
    public function checkDependencyWhenEnableModule($moduleName)
    {
        $dependenciesMissing = [];
        $graph = $this->factory->create($this->modules);
        $graph->traverseGraph($moduleName);
        foreach ($this->modules as $module) {
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
        return $dependenciesMissing;
    }
}
