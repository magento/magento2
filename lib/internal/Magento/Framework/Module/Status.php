<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module;

use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;

/**
 * A service for controlling module status
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Status
{
    /**
     * Module list loader
     *
     * @var ModuleList\Loader
     */
    private $loader;

    /**
     * Module list
     *
     * @var ModuleList
     */
    private $list;

    /**
     * Deployment config writer
     *
     * @var Writer
     */
    private $writer;

    /**
     * Dependency Checker
     *
     * @var DependencyChecker
     */
    private $dependencyChecker;

    /**
     * Conflict checker
     *
     * @var ConflictChecker
     */
    private $conflictChecker;

    /**
     * Constructor
     *
     * @param ModuleList\Loader $loader
     * @param ModuleList $list
     * @param Writer $writer
     * @param ConflictChecker $conflictChecker
     * @param DependencyChecker $dependencyChecker
     */
    public function __construct(
        ModuleList\Loader $loader,
        ModuleList $list,
        Writer $writer,
        ConflictChecker $conflictChecker,
        DependencyChecker $dependencyChecker
    ) {
        $this->loader = $loader;
        $this->list = $list;
        $this->writer = $writer;
        $this->conflictChecker = $conflictChecker;
        $this->dependencyChecker = $dependencyChecker;
    }

    /**
     * Whether it is allowed to enable or disable specified modules
     *
     * @param bool $isEnabled
     * @param string[] $modulesToBeChanged
     * @param string[] $currentlyEnabledModules
     * @param bool $prettyMessage
     *
     * @return string[]
     */
    public function checkConstraints(
        $isEnabled,
        $modulesToBeChanged,
        $currentlyEnabledModules = null,
        $prettyMessage = false
    ) {
        $errorMessages = [];
        if ($isEnabled) {
            $errorModulesDependency = $this->dependencyChecker->checkDependenciesWhenEnableModules(
                $modulesToBeChanged,
                $currentlyEnabledModules
            );
            $errorModulesConflict = $this->conflictChecker->checkConflictsWhenEnableModules(
                $modulesToBeChanged,
                $currentlyEnabledModules
            );
        } else {
            $errorModulesDependency = $this->dependencyChecker->checkDependenciesWhenDisableModules(
                $modulesToBeChanged,
                $currentlyEnabledModules
            );
            $errorModulesConflict = [];
        }

        foreach ($errorModulesDependency as $moduleName => $missingDependencies) {
            if (!empty($missingDependencies)) {
                if ($prettyMessage) {
                    $errorMessages[] = $this->createShortErrorMessage($isEnabled, $moduleName);
                } else {
                    $errorMessages = array_merge(
                        $errorMessages,
                        $this->createVerboseErrorMessage($isEnabled, $moduleName, $missingDependencies)
                    );
                }
            }
        }

        foreach ($errorModulesConflict as $moduleName => $conflictingModules) {
            if (!empty($conflictingModules)) {
                $errorMessages[] = "Cannot enable $moduleName because it conflicts with other modules:";
                $errorMessages[] = implode("\n", $conflictingModules);
            }
        }

        return $errorMessages;
    }

    /**
     * Sets specified modules to enabled or disabled state
     *
     * Performs other necessary routines, such as cache cleanup
     *
     * @param bool $isEnabled
     * @param string[] $modules
     * @return void
     */
    public function setIsEnabled($isEnabled, $modules)
    {
        $result = [];
        foreach ($this->getAllModules($modules) as $name) {
            $currentStatus = $this->list->has($name);
            if (in_array($name, $modules)) {
                $result[$name] = (int)$isEnabled;
            } else {
                $result[$name] = (int)$currentStatus;
            }
        }
        $this->writer->saveConfig([ConfigFilePool::APP_CONFIG => ['modules' => $result]], true);
    }

    /**
     * Get a list of modules that will be changed
     *
     * @param bool $isEnabled
     * @param string[] $modules
     * @return string[]
     */
    public function getModulesToChange($isEnabled, $modules)
    {
        $changed = [];
        foreach ($this->getAllModules($modules) as $name) {
            $currentStatus = $this->list->has($name);
            if (in_array($name, $modules)) {
                if ($isEnabled != $currentStatus) {
                    $changed[] = $name;
                }
            }
        }
        return $changed;
    }

    /**
     * Gets all modules and filters against the specified list
     *
     * @param string[] $modules
     * @return string[]
     * @throws \LogicException
     */
    private function getAllModules($modules)
    {
        $all = $this->loader->load();
        $unknown = [];
        foreach ($modules as $name) {
            if (!isset($all[$name])) {
                $unknown[] = $name;
            }
        }
        if ($unknown) {
            throw new \LogicException("Unknown module(s): '" . implode("', '", $unknown) . "'");
        }
        return array_keys($all);
    }

    /**
     * Creates a one-line error message that a module cannot be enabled/disabled.
     *
     * @param bool $isEnabled
     * @param string $moduleName
     * @return string
     */
    private function createShortErrorMessage($isEnabled, $moduleName)
    {
        if ($isEnabled) {
            return "Cannot enable $moduleName";
        } else {
            return "Cannot disable $moduleName";
        }
    }

    /**
     * Creates a verbose error message that a module cannot be enabled/disabled.
     *
     * Each line in the error message will be an array element.
     *
     * @param bool $isEnabled
     * @param string $moduleName
     * @param array $missingDependencies
     * @return string[]
     */
    private function createVerboseErrorMessage($isEnabled, $moduleName, $missingDependencies)
    {
        if ($isEnabled) {
            $errorMessages[] = "Cannot enable $moduleName because it depends on disabled modules:";
        } else {
            $errorMessages[] = "Cannot disable $moduleName because modules depend on it:";
        }
        foreach ($missingDependencies as $errorModule => $path) {
                $errorMessages[] = "$errorModule: " . implode('->', $path);
        }
        return $errorMessages;
    }
}
