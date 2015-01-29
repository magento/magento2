<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Package\Version\VersionParser;

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
     * Check if two modules are conflicted
     *
     * @param string $moduleA
     * @param string $moduleB
     * @return bool
     */
    private function checkIfConflict($moduleA, $moduleB)
    {
        $versionParser = new VersionParser();
        if (isset($this->packageInfo->getConflict($moduleB)[$moduleA]) && $this->packageInfo->getVersion($moduleA)) {
            $constraintA = $versionParser->parseConstraints($this->packageInfo->getConflict($moduleB)[$moduleA]);
            $constraintB = $versionParser->parseConstraints($this->packageInfo->getVersion($moduleA));
            if ($constraintA->matches($constraintB)) {
                return true;
            }
        }
        if (isset($this->packageInfo->getConflict($moduleA)[$moduleB]) && $this->packageInfo->getVersion($moduleB)) {
            $constraintA = $versionParser->parseConstraints($this->packageInfo->getConflict($moduleA)[$moduleB]);
            $constraintB = $versionParser->parseConstraints($this->packageInfo->getVersion($moduleB));
            if ($constraintA->matches($constraintB)) {
                return true;
            }
        }
        return false;
    }
}
