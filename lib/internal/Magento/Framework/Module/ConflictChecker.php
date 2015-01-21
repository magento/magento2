<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Filesystem;

class ConflictChecker extends Checker
{
    /**
     * Key to conflicting packages array in composer.json files
     */
    const KEY_CONFLICT = 'conflict';

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Check if enabling module will conflict any modules
     *
     * @param string[] $moduleNames
     * @return array
     */
    public function checkConflictsWhenEnableModules($moduleNames)
    {
        // union of currently enabled modules and to-be-enabled modules
        $this->enabledModules = array_unique(array_merge($this->enabledModules, $moduleNames));
        $conflictsAll = [];
        foreach ($moduleNames as $moduleName) {
            $conflicts = [];
            foreach ($this->enabledModules as $enabledModule) {
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
        $jsonDecoder = new \Magento\Framework\Json\Decoder();

        $data1 = $jsonDecoder->decode($this->modulesData[$enabledModule]);
        $data2 = $jsonDecoder->decode($this->modulesData[$moduleName]);

        if (isset($data1[self::KEY_CONFLICT])) {
            foreach (array_keys($data1[self::KEY_CONFLICT]) as $packageName) {
                $module = $this->mapper->packageNameToModuleFullName($packageName);
                if ($module == $moduleName) {
                    return true;
                }
            }
        }

        if (isset($data2[self::KEY_CONFLICT])) {
            foreach (array_keys($data2[self::KEY_CONFLICT]) as $packageName) {
                $module = $this->mapper->packageNameToModuleFullName($packageName);
                if ($module == $enabledModule) {
                    return true;
                }
            }
        }
        return false;
    }
}
