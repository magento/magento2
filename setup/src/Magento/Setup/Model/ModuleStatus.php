<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Setup\Model;

use Magento\Framework\Module\ModuleList\Loader as ModuleLoader;
use Magento\Framework\App\DeploymentConfig\Reader as ConfigReader;

class ModuleStatus
{
    /**
     * List of Modules
     *
     * @var array
     */
    protected $allModules;

    /**
     * Configuration Reader
     *
     * @var ConfigReader
     */
    protected $configReader;

    /**
     * Constructor
     *
     * @param ModuleLoader $moduleLoader
     * @param ConfigReader $configReader
     */
    public function __construct(ModuleLoader $moduleLoader, ConfigReader $configReader)
    {
        $this->allModules = $moduleLoader->load();
        $this->configReader = $configReader;
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

            $existingModules = $this->configReader->load();
            if (isset($existingModules['modules'])) {
                $existingModules = $existingModules['modules'];
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
