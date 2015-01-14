<?php
namespace Magento\Framework\Module;

class ConflictChecker
{
    const KEY_CONFLICT = 'conflict';

    /**
     * @var array
     */
    private $modules;

    /**
     * @var array
     */
    private $enabledModules;

    /**
     * @param array $modules
     * @param array $enabledModules
     */
    public function __construct(array $modules, array $enabledModules)
    {
        $this->modules = $modules;
        $this->enabledModules = $enabledModules;
    }

    /**
     * @param $moduleName
     * @return array
     */
    public function checkConflictWhenEnableModule($moduleName)
    {
        $conflicts = [];
        foreach (array_keys($this->getMagentoConflicts($moduleName)) as $module) {
            if ($this->checkIfEnabled($this->getRealName($module))) {
                $conflicts[] = $this->getRealName($module);
            }
        }
        return $conflicts;
    }

    /**
     * @param $moduleName
     * @return mixed
     */
    public function getMagentoConflicts($moduleName)
    {
        $jsonDecoder = new \Magento\Framework\Json\Decoder();
        // workaround: convert Magento_X to X
        $module = substr($moduleName, 8);
        $data = $jsonDecoder->decode(file_get_contents(BP . '/app/code/Magento/' . $module . '/composer.json'));
        return $data[self::KEY_CONFLICT];
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