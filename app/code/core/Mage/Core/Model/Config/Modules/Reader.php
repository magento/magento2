<?php
/**
 * Module configuration file reader
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Config_Modules_Reader
{
    /**
     * Modules configuration
     *
     * @var Mage_Core_Model_Config_Modules
     */
    protected $_config;

    /**
     * Module file reader
     *
     * @var Mage_Core_Model_Config_Loader_Modules_File
     */
    protected $_fileReader;

    /**
     * @param Mage_Core_Model_Config_Modules $modulesConfig
     * @param Mage_Core_Model_Config_Loader_Modules_File $fileReader
     */
    public function __construct(
        Mage_Core_Model_Config_Modules $modulesConfig,
        Mage_Core_Model_Config_Loader_Modules_File $fileReader
    ) {
        $this->_config = $modulesConfig;
        $this->_fileReader = $fileReader;
    }

    /**
     * Iterate all active modules "etc" folders and combine data from
     * specidied xml file name to one object
     *
     * @param   string $fileName
     * @param   null|Mage_Core_Model_Config_Base $mergeToObject
     * @param   null|Mage_Core_Model_Config_Base $mergeModel
     * @return  Mage_Core_Model_Config_Base
     */
    public function loadModulesConfiguration($fileName, $mergeToObject = null, $mergeModel = null)
    {
        return $this->_fileReader->loadConfigurationFromFile($this->_config, $fileName, $mergeToObject, $mergeModel);
    }

    /**
     * Go through all modules and find configuration files of active modules
     *
     * @param string $filename
     * @return array
     */
    public function getModuleConfigurationFiles($filename)
    {
        return $this->_fileReader->getConfigurationFiles($this->_config, $filename);
    }

    /**
     * Get module directory by directory type
     *
     * @param   string $type
     * @param   string $moduleName
     * @return  string
     */
    public function getModuleDir($type, $moduleName)
    {
        return $this->_fileReader->getModuleDir($this->_config, $type, $moduleName);
    }

    /**
     * Set path to the corresponding module directory
     *
     * @param string $moduleName
     * @param string $type directory type (etc, controllers, locale etc)
     * @param string $path
     */
    public function setModuleDir($moduleName, $type, $path)
    {
        $this->_fileReader->setModuleDir($moduleName, $type, $path);
    }
}
