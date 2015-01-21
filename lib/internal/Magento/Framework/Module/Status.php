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
     * Module Directory Reader
     *
     * @var Dir\Reader
     */
    private $reader;

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
        Dir\Reader $reader,
        Writer $writer,
        Cleanup $cleanup,
        ConflictChecker $conflictChecker,
        DependencyChecker $dependencyChecker
    ) {
        $this->loader = $loader;
        $this->list = $list;
        $this->reader = $reader;
        $this->writer = $writer;
        $this->cleanup = $cleanup;
        $this->conflictChecker = $conflictChecker;
        $this->dependencyChecker = $dependencyChecker;
    }

    /**
     * Whether it is allowed to enable or disable specified modules
     *
     * @param bool $isEnable
     * @param string[] $modules
     * @return string[]
     */
    public function checkConstraints($isEnable, $modules)
    {
        $enabledModules = $this->list->getNames();
        // array keys: module name in module.xml; array values: raw content from composer.json
        // this raw data is used to create a dependency graph and also a package name-module name mapping
        $rawData = array_combine(array_keys($this->loader->load()), $this->reader->getComposerJsonFiles()->toArray());
        $errorMessages = [];

        $this->dependencyChecker->setModulesData($rawData);
        $this->dependencyChecker->setEnabledModules($enabledModules);
        if ($isEnable) {
            $this->conflictChecker->setModulesData($rawData);
            $this->conflictChecker->setEnabledModules($enabledModules);

            $errorModulesDependency = $this->dependencyChecker->checkDependenciesWhenEnableModules($modules);
            $errorModulesConflict = $this->conflictChecker->checkConflictsWhenEnableModules($modules);

            foreach ($errorModulesDependency as $moduleName => $missingDependencies) {
                if (!empty($missingDependencies)) {
                    $errorMessages[] = "Cannot enable $moduleName, depending on inactive modules:";
                    foreach ($missingDependencies as $errorModule => $path) {
                        $errorMessages [] = "\t$errorModule: " . implode('->', $path);
                    }
                }
            }
            foreach ($errorModulesConflict as $moduleName => $conflictingModules) {
                if (!empty($conflictingModules)) {
                    $errorMessages[] = "Cannot enable $moduleName, conflicting active modules:";
                    foreach ($conflictingModules as $conflictingModule) {
                        $errorMessages [] = "\t$conflictingModule";
                    }
                }
            }
        } else {
            $errorModulesDependency = $this->dependencyChecker->checkDependenciesWhenDisableModules($modules);

            foreach ($errorModulesDependency as $moduleName => $missingDependencies) {
                if (!empty($missingDependencies)) {
                    $errorMessages[] = "Cannot disable $moduleName, active modules depending on it:";
                    foreach ($missingDependencies as $errorModule => $path) {
                        $errorMessages [] = "\t$errorModule: " . implode('->', $path);
                    }
                }
            }
        }

        return $errorMessages;
    }

    /**
     * Sets specified modules to enabled or disabled state
     *
     * Performs other necessary routines, such as cache cleanup
     * Returns list of modules that have changed
     *
     * @param bool $isEnabled
     * @param string[] $modules
     * @return string[]
     */
    public function setIsEnabled($isEnabled, $modules)
    {
        $result = [];
        $changed = [];
        foreach ($this->getAllModules($modules) as $name) {
            $currentStatus = $this->list->has($name);
            if (in_array($name, $modules)) {
                $result[$name] = $isEnabled;
                if ($isEnabled != $currentStatus) {
                    $changed[] = $name;
                }
            } else {
                $result[$name] = $currentStatus;
            }
        }
        if ($changed) {
            $segment = new ModuleList\DeploymentConfig($result);
            $this->writer->update($segment);
            $this->cleanup->clearCaches();
            $this->cleanup->clearCodeGeneratedFiles();
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
}
