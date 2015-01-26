<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module;

use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\State\Cleanup;

/**
 * A service for controlling module status
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
     * Application state cleanup service
     *
     * @var Cleanup
     */
    private $cleanup;

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
     * @param Cleanup $cleanup
     * @param ConflictChecker $conflictChecker
     * @param DependencyChecker $dependencyChecker
     */
    public function __construct(
        ModuleList\Loader $loader,
        ModuleList $list,
        Writer $writer,
        Cleanup $cleanup,
        ConflictChecker $conflictChecker,
        DependencyChecker $dependencyChecker
    ) {
        $this->loader = $loader;
        $this->list = $list;
        $this->writer = $writer;
        $this->cleanup = $cleanup;
        $this->conflictChecker = $conflictChecker;
        $this->dependencyChecker = $dependencyChecker;
    }

    /**
     * Whether it is allowed to enable or disable specified modules
     *
     * @param bool $isEnabled
     * @param string[] $modules
     * @return string[]
     */
    public function checkConstraints($isEnabled, $modules)
    {
        $errorMessages = [];
        if ($isEnabled) {
            $errorModulesDependency = $this->dependencyChecker->checkDependenciesWhenEnableModules($modules);
            $errorModulesConflict = $this->conflictChecker->checkConflictsWhenEnableModules($modules);
        } else {
            $errorModulesDependency = $this->dependencyChecker->checkDependenciesWhenDisableModules($modules);
            $errorModulesConflict = [];
        }

        foreach ($errorModulesDependency as $moduleName => $missingDependencies) {
            if (!empty($missingDependencies)) {
                $errorMessages[] = $isEnabled ?
                    "Cannot enable $moduleName, depending on disabled modules:" :
                    "Cannot disable $moduleName, modules depending on it:";
                foreach ($missingDependencies as $errorModule => $path) {
                    $errorMessages [] = "$errorModule: " . implode('->', $path);
                }
            }
        }

        foreach ($errorModulesConflict as $moduleName => $conflictingModules) {
            if (!empty($conflictingModules)) {
                $errorMessages[] = "Cannot enable $moduleName, conflicting modules:";
                $errorMessages[] = implode(', ', $conflictingModules);
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
                $result[$name] = $isEnabled;
            } else {
                $result[$name] = $currentStatus;
            }
        }
        $segment = new ModuleList\DeploymentConfig($result);
        $this->writer->update($segment);
        $this->cleanup->clearCaches();
        $this->cleanup->clearCodeGeneratedFiles();
    }

    /**
     * Get a list of modules that will not be changed
     *
     * @param bool $isEnabled
     * @param string[] $modules
     * @return string[]
     */
    public function getUnchangedModules($isEnabled, $modules)
    {
        $unchanged = [];
        foreach ($this->getAllModules($modules) as $name) {
            $currentStatus = $this->list->has($name);
            if (in_array($name, $modules)) {
                if ($isEnabled == $currentStatus) {
                    $unchanged[] = $name;
                }
            }
        }
        return $unchanged;
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
}
