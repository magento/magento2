<?php
namespace Magento\Framework\Module;

class DependencyChecker
{
    /**
     * @var DependencyGraphFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $modules;

    /**
     * @var array
     */
    private $enabledModules;

    /**
     * @param DependencyGraphFactory $factory
     * @param array $modules
     * @param array $enabledModules
     */
    public function __construct(DependencyGraphFactory $factory, array $modules, array $enabledModules)
    {
        $this->factory = $factory;
        $this->modules = $modules;
        $this->enabledModules = $enabledModules;
    }

    /**
     * Checks modules that are depending on the to-be-disabled module
     *
     * @param string $moduleName
     * @return array
     */
    public function checkDependencyWhenDisableModule($moduleName)
    {
        // workarounds: convert Magento_X to x
        $moduleName = strtolower(substr($moduleName, 8));
        $dependenciesMissing = [];
        $graph = $this->factory->create($this->modules);
        foreach ($this->modules as $module) {
            // workaround: convert Magento_X to x
            if ($this->checkIfEnabled($module) && strtolower(substr($module, 8)) !== $moduleName) {
                if (!empty($graph->dfs(strtolower(substr($module, 8)), $moduleName)) &&
                    $this->checkIfEnabled($module)
                ) {
                    $dependenciesMissing[] = $module;
                }
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
        // workarounds: convert Magento_X to X
        $moduleName = substr($moduleName, 8);
        $dependenciesMissing = [];
        foreach ($this->getMagentoDependencies($moduleName) as $module) {
            if (!$this->checkIfEnabled($this->getRealName($module))) {
                $dependenciesMissing[] = $this->getRealName($module);
            }
        }
        return $dependenciesMissing;
    }

    /**
     * Get array of modules that a module depends on
     *
     * @param string $moduleName
     * @return array
     */
    private function getMagentoDependencies($moduleName)
    {
        $jsonDecoder = new \Magento\Framework\Json\Decoder();
        $data = $jsonDecoder->decode(file_get_contents(BP . '/app/code/Magento/' . $moduleName . '/composer.json'));
        $dependencies = [];
        // get rid of non Magento dependencies
        foreach (array_keys($data[DependencyGraphFactory::KEY_REQUIRE]) as $depend) {
            if (strpos($depend, DependencyGraphFactory::ALIAS_PREFIX) === 0) {
                $dependencies[] = $depend;
            }
        }
        return $dependencies;
    }

    /**
     * Check if module is enabled
     *
     * @param string $moduleName
     * @return bool
     */
    private function checkIfEnabled($moduleName)
    {
        return array_search($moduleName, $this->enabledModules) !== false;
    }

    /**
     * Convert alias used in composer.json to Magento_X format
     *
     * @param $alias
     * @return string
     */
    private function getRealName($alias)
    {
        // workaround: convert composer.json alias to magento_x
        $lowerCaseModuleName = 'magento_' . str_replace('-', '', substr($alias, strlen(DependencyGraphFactory::ALIAS_PREFIX)));
        foreach ($this->modules as $module) {
            if (strtolower($module) == $lowerCaseModuleName) {
                return $module;
            }
        }
    }
}
