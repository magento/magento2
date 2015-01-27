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

class ModuleStatus {

    /**
     * List of Modules
     *
     * @var ModuleLoader
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
        foreach ($allModules as $module => $value) {
            $allModules[$module]['select'] = true;
            $allModules[$module]['mvp'] = false;
        }

        $existingModules = $this->configReader->load();
        if (isset($existingModules['modules'])){
            $existingModules = $existingModules['modules'];
            foreach ($existingModules as $module => $value) {
                if(!$value){
                    $allModules[$module]['select'] = false;
                }
            }
        }

        $mvpModules = $this->getMVPModules();
        if (isset($mvpModules)){
            foreach ($mvpModules as $module) {
                $allModules[$module]['mvp'] = true;
            }
        }
        ksort($allModules);
        return $allModules;
    }

    /**
     * Returns list of MVP modules
     *
     * @return array
     */
    private function getMVPModules()
    {
        //for example will be implemented in MAGETWO-33222
        return ['Magento_Core', 'Magento_Store', 'Magento_AdminNotification'];
    }
}
