<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Setup\Model;

use Magento\Framework\Module\ModuleList\Loader as ModuleLoader;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Module\ModuleList\DeploymentConfig as ModuleDeployment;

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
     * Constructor
     *
     * @param ModuleLoader $moduleLoader
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(ModuleLoader $moduleLoader, DeploymentConfig $deploymentConfig)
    {
        $this->allModules = $moduleLoader->load();
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Returns list of Modules to be displayed
     *
     * @return array
     */
    public function getAllModules()
    {
        $allModules = $this->allModules;
        if (isset($allModules)) {
            foreach ($allModules as $module => $value) {
                $allModules[$module]['selected'] = true;
            }

            $existingModules = $this->deploymentConfig->getSegment(ModuleDeployment::CONFIG_KEY);
            if (isset($existingModules)) {
                foreach ($existingModules as $module => $value) {
                    if(!$value) {
                        $allModules[$module]['selected'] = false;
                    }
                }
            }

            ksort($allModules);
            return $allModules;
        }
        return [];
    }
}
