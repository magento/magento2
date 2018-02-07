<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Magento\Setup\Model;

use Magento\Framework\Module\ModuleList\Loader as ModuleLoader;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Module\DependencyChecker;
use Magento\Framework\Config\ConfigOptionsListConstants;

class ModuleStatus
{
    /**
     * List of Modules
     *
     * @var array
     */
    protected $allModules;

    /**
     * Deployment Config
     *
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * Dependency Checker
     *
     * @var DependencyChecker
     */
    private $dependencyChecker;

    /**
     * Constructor
     *
     * @param ModuleLoader $moduleLoader
     * @param DeploymentConfig $deploymentConfig
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        ModuleLoader $moduleLoader,
        DeploymentConfig $deploymentConfig,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->allModules = $moduleLoader->load();
        foreach (array_keys($this->allModules) as $module) {
            $this->allModules[$module]['selected'] = true;
            $this->allModules[$module]['disabled'] = true;
        }
        $this->deploymentConfig = $deploymentConfig;
        $this->dependencyChecker = $objectManagerProvider->get()->get('Magento\Framework\Module\DependencyChecker');
    }

    /**
     * Returns list of Modules to be displayed
     *
     * @param array $selectedModules
     * @return array
     */
    public function getAllModules(array $selectedModules = null)
    {
        if (isset($this->allModules)) {
            if (isset($selectedModules)) {
                $diff = array_diff(array_keys($this->allModules), $selectedModules);
                foreach ($diff as $module) {
                    $this->allModules[$module]['selected'] = false;
                }
            } else {
                $this->deselectDisabledModules();
            }
            $disableModules = $this->getListOfDisableModules();
            if (isset($disableModules)) {
                foreach ($disableModules as $module) {
                    $this->allModules[$module]['disabled'] = false;
                }
            }
            //check if module is not checked and disabled - possible when config is incorrectly modified.
            foreach ($this->allModules as $module) {
                if (!$module['selected'] && $module['disabled']) {
                    $this->allModules[$module['name']]['disabled'] = false;
                }
            }
            return $this->allModules;
        }
        return [];
    }

    /**
     * Returns list of modules that can be disabled
     *
     * @return array
     */
    private function getListOfDisableModules()
    {
        $canBeDisabled = [];
        $enabledModules = $this->getListOfEnabledModules();
        foreach ($this->allModules as $module) {
            $errorMessages = $this->dependencyChecker->checkDependenciesWhenDisableModules(
                [$module['name']],
                $enabledModules
            );
            if (sizeof($errorMessages[$module['name']]) === 0) {
                $canBeDisabled[] = $module['name'];
            }
        }
        return $canBeDisabled;
    }

    /**
     * Returns list of enabled modules
     *
     * @return array
     */
    private function getListOfEnabledModules()
    {
        $enabledModules = [];
        foreach ($this->allModules as $module) {
            if ($module['selected']) {
                $enabledModules[] =  $module['name'];
            }
        }
        return $enabledModules;
    }

    /**
     * @param bool $status
     * @param String $moduleName
     *
     * @return void
     */
    public function setIsEnabled($status, $moduleName)
    {
        $this->allModules[$moduleName]['selected'] = $status;
    }

    /**
     * Marks modules that are disabled in deploymentConfig as unselected.
     *
     * @return void
     */
    private function deselectDisabledModules()
    {
        $existingModules = $this->deploymentConfig->get(ConfigOptionsListConstants::KEY_MODULES);
        if (isset($existingModules)) {
            foreach ($existingModules as $module => $value) {
                if (!$value) {
                    $this->allModules[$module]['selected'] = false;
                }
            }
        }
    }
}
