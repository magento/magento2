<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

/**
 * Checks for conflicts between modules
 */
class ConflictChecker
{
    /**
     * Enabled module list
     *
     * @var ModuleList
     */
    private $list;

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
     * @param PackageInfoFactory $packageInfoFactory
     */
    public function __construct(ModuleList $list, PackageInfoFactory $packageInfoFactory)
    {
        $this->list = $list;
        $this->packageInfo = $packageInfoFactory->create();
    }

    /**
     * Check if enabling module will conflict any modules
     *
     * @param string[] $moduleNames
     * @return array
     */
    public function checkConflictsWhenEnableModules($moduleNames)
    {
        // union of currently enabled modules and to-be-enabled modules
        $enabledModules = array_unique(array_merge($this->list->getNames(), $moduleNames));
        $conflictsAll = [];
        foreach ($moduleNames as $moduleName) {
            $conflicts = [];
            foreach ($enabledModules as $enabledModule) {
                if ($this->checkIfConflict($enabledModule, $moduleName)) {
                    $conflicts[] = $enabledModule;
                }
            }
            $conflictsAll[$moduleName] = $conflicts;
        }
        return $conflictsAll;
    }

    /**
     * Check if module is conflicted
     *
     * @param string $enabledModule
     * @param string $moduleName
     * @return bool
     */
    private function checkIfConflict($enabledModule, $moduleName)
    {
        if (array_search($enabledModule, $this->packageInfo->getConflict($moduleName)) !== false) {
            return true;
        }
        if (array_search($moduleName, $this->packageInfo->getConflict($enabledModule)) !== false) {
            return true;
        }

        return false;
    }
}
