<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

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
     * @param string[] $currentlyEnabledModules
     *
     * @return array
     */
    public function checkConflictsWhenEnableModules($moduleNames, $currentlyEnabledModules = null)
    {
        $masterList = isset($currentlyEnabledModules) ? $currentlyEnabledModules: $this->list->getNames();
        // union of currently enabled modules and to-be-enabled modules
        $enabledModules = array_unique(array_merge($masterList, $moduleNames));
        $conflictsAll = [];
        foreach ($moduleNames as $moduleName) {
            $conflicts = [];
            foreach ($enabledModules as $enabledModule) {
                $messages = $this->getConflictMessages($enabledModule, $moduleName);
                if (!empty($messages)) {
                    $conflicts[] = implode("\n", $messages);
                }
            }
            $conflictsAll[$moduleName] = $conflicts;
        }
        return $conflictsAll;
    }

    /**
     * Check if two modules are conflicted and get the message for display
     *
     * @param string $moduleA
     * @param string $moduleB
     * @return string[]
     */
    private function getConflictMessages($moduleA, $moduleB)
    {
        $messages = [];
        $versionParser = new VersionParser();
        if (isset($this->packageInfo->getConflict($moduleB)[$moduleA]) &&
            $this->packageInfo->getConflict($moduleB)[$moduleA] &&
            $this->packageInfo->getVersion($moduleA)
        ) {
            $constraintA = $versionParser->parseConstraints($this->packageInfo->getConflict($moduleB)[$moduleA]);
            $constraintB = $versionParser->parseConstraints($this->packageInfo->getVersion($moduleA));
            if ($constraintA->matches($constraintB)) {
                $messages[] = "$moduleB conflicts with current $moduleA version " .
                    $this->packageInfo->getVersion($moduleA) .
                    ' (version should not be ' . $this->packageInfo->getConflict($moduleB)[$moduleA] . ')';
            }
        }
        if (isset($this->packageInfo->getConflict($moduleA)[$moduleB]) &&
            $this->packageInfo->getConflict($moduleA)[$moduleB] &&
            $this->packageInfo->getVersion($moduleB)
        ) {
            $constraintA = $versionParser->parseConstraints($this->packageInfo->getConflict($moduleA)[$moduleB]);
            $constraintB = $versionParser->parseConstraints($this->packageInfo->getVersion($moduleB));
            if ($constraintA->matches($constraintB)) {
                $messages[] = "$moduleA conflicts with current $moduleB version " .
                    $this->packageInfo->getVersion($moduleA) .
                    ' (version should not be ' . $this->packageInfo->getConflict($moduleA)[$moduleB] . ')';
            }
        }
        return $messages;
    }
}
