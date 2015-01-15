<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module;

use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\State\Cleanup;
use Magento\Framework\App\DeploymentConfig;

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
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Constructor
     *
     * @param ModuleList\Loader $loader
     * @param ModuleList $list
     * @param Writer $writer
     * @param Cleanup $cleanup
     */
    public function __construct(ModuleList\Loader $loader, ModuleList $list, Writer $writer, Cleanup $cleanup, DeploymentConfig $deploymentConfig)
    {
        $this->loader = $loader;
        $this->list = $list;
        $this->writer = $writer;
        $this->cleanup = $cleanup;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Whether it is allowed to enable or disable specified modules
     *
     * TODO: not implemented yet (MAGETWO-32613)
     *
     * @param bool $isEnable
     * @param string[] $modules
     * @return string[]
     */
    public function checkConstraints($isEnable, $modules)
    {
        // TODO: deploymentConfig only works when application exists
        $enabledModules = [];
        $all = $this->deploymentConfig->getSegment(ModuleList\DeploymentConfig::CONFIG_KEY);
        foreach ($all as $module => $enabled) {
            if ($enabled) {
                $enabledModules[] = $module;
            }
        }

        $errorMessages = [];

        if ($isEnable) {
            $dependencyChecker = new DependencyChecker(
                new DependencyGraphFactory(),
                array_keys($all),
                array_unique(array_merge($enabledModules, $modules)) // union, consider to-be-enable modules
            );
            foreach ($modules as $moduleName) {
                $errorModules = $dependencyChecker->checkDependencyWhenEnableModule($moduleName);
                if (!empty($errorModules)) {
                    $errorMessages[] = "Cannot enable $moduleName, depending on inactive modules:";
                    foreach ($errorModules as $errorModule) {
                        $errorMessages [] = "\t$errorModule";
                    }
                }
            }
            // TODO: consolidate to one for loop
            $conflictChecker = new ConflictChecker(array_keys($all), array_unique(array_merge($enabledModules, $modules)));
            foreach ($modules as $moduleName) {
                $errorModules = $conflictChecker->checkConflictWhenEnableModule($moduleName);
                if (!empty($errorModules)) {
                    $errorMessages[] = "Cannot enable $moduleName, conflicting active modules:";
                    foreach ($errorModules as $errorModule) {
                        $errorMessages [] = "\t$errorModule";
                    }
                }
            }
        } else {
            $dependencyChecker = new DependencyChecker(
                new DependencyGraphFactory(),
                array_keys($all),
                $enabledModules
            );
            foreach ($modules as $moduleName) {
                $errorModules = $dependencyChecker->checkDependencyWhenDisableModule($moduleName);
                if (!empty($errorModules)) {
                    $errorMessages[] = "Cannot disable $moduleName, active modules depending on it:";
                    foreach ($errorModules as $errorModule) {
                        $errorMessages [] = "\t$errorModule";
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
