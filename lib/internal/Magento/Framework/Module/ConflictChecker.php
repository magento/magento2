<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Filesystem;

class ConflictChecker
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
     * Check if enabling module will conflict any modules
     *
     * @param string[] $moduleNames
     * @return array
     */
    public function checkConflictsWhenEnableModules($moduleNames)
    {
        // union of currently enabled modules and to-be-enabled modules
        $enabledModules = array_unique(array_merge($this->packageInfo->getEnabledModules(), $moduleNames));
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
     * @param string $moduleName
     * @return bool
     */
    private function checkIfConflict($enabledModule, $moduleName)
    {
        if (array_search($this->packageInfo->getPackageName($enabledModule),
                $this->packageInfo->getConflict($moduleName)) !== false) {
            return true;
        }
        if (array_search($this->packageInfo->getPackageName($moduleName),
                $this->packageInfo->getConflict($enabledModule)) !== false) {
            return true;
        }

        return false;
    }
}
