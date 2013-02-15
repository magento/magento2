<?php
/**
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
class Mage_Core_Model_Config_Loader_Modules_File
{
    /**
     * Modules configuration
     *
     * @var Mage_Core_Model_Config_Modules
     */
    protected $_modulesConfig;

    /**
     * Base config factory
     *
     * @var Mage_Core_Model_Config_BaseFactory
     */
    protected $_prototypeFactory;

    /**
     * Module configuration directories
     *
     * @var array
     */
    protected $_moduleDirs = array();

    /**
     * Directory registry
     *
     * @var Mage_Core_Model_Dir
     */
    protected $_dirs;

    /**
     * @param Mage_Core_Model_Dir $dirs
     * @param Mage_Core_Model_Config_BaseFactory $prototypeFactory
     */
    public function __construct(
        Mage_Core_Model_Dir $dirs,
        Mage_Core_Model_Config_BaseFactory $prototypeFactory
    ) {
        $this->_dirs = $dirs;
        $this->_prototypeFactory = $prototypeFactory;
    }

    /**
     * Iterate all active modules "etc" folders and combine data from
     * specidied xml file name to one object
     *
     * @param Mage_Core_Model_ConfigInterface $modulesConfig
     * @param string $fileName
     * @param Mage_Core_Model_Config_Base|null $mergeToObject
     * @param Mage_Core_Model_Config_Base|null $mergeModel
     * @param array $configCache
     * @return Mage_Core_Model_Config_Base|null
     */
    public function loadConfigurationFromFile(
        Mage_Core_Model_ConfigInterface $modulesConfig,
        $fileName,
        $mergeToObject = null,
        $mergeModel = null,
        $configCache = array()
    ) {
        if ($mergeToObject === null) {
            $mergeToObject = $this->_prototypeFactory->create('<config/>');
        }
        if ($mergeModel === null) {
            $mergeModel = $this->_prototypeFactory->create('<config/>');
        }
        $modules = $modulesConfig->getNode('modules')->children();
        /** @var $module Varien_Simplexml_Element */
        foreach ($modules as $modName => $module) {
            if ($module->is('active')) {
                if (!is_array($fileName)) {
                    $fileName = array($fileName);
                }
                foreach ($fileName as $configFile) {
                    $this->_loadFileConfig(
                        $configFile, $configCache, $modName, $mergeToObject, $modulesConfig, $mergeModel
                    );
                }
            }
        }
        return $mergeToObject;
    }

    /**
     * Load configuration from single file
     *
     * @param string $configFile
     * @param array $configCache
     * @param string $modName
     * @param Mage_Core_Model_Config_Base $mergeToObject
     * @param Mage_Core_Model_ConfigInterface $modulesConfig
     * @param Mage_Core_Model_Config_Base $mergeModel
     */
    public function _loadFileConfig(
        $configFile, $configCache, $modName, $mergeToObject, Mage_Core_Model_ConfigInterface $modulesConfig, $mergeModel
    ) {
        if ($configFile == 'config.xml' && isset($configCache[$modName])) {
            $mergeToObject->extend($configCache[$modName], true);
            //Prevent overriding <active> node of module if it was redefined in etc/modules
            $mergeToObject->extend(
                $this->_prototypeFactory->create(
                    "<config><modules><{$modName}><active>true</active></{$modName}></modules></config>"
                ),
                true
            );
        } else {
            $configFilePath = $this->getModuleDir($modulesConfig, 'etc', $modName) . DS . $configFile;
            if ($mergeModel->loadFile($configFilePath)) {
                $mergeToObject->extend($mergeModel, true);
            }
        }
    }

    /**
     * Go through all modules and find configuration files of active modules
     *
     * @param Mage_Core_Model_ConfigInterface $modulesConfig
     * @param $filename
     * @return array
     */
    public function getConfigurationFiles(Mage_Core_Model_ConfigInterface $modulesConfig, $filename)
    {
        $result = array();
        $modules = $modulesConfig->getNode('modules')->children();
        foreach ($modules as $moduleName => $module) {
            if ((!$module->is('active'))) {
                continue;
            }
            $file = $this->getModuleDir($modulesConfig, 'etc', $moduleName) . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($file)) {
                $result[] = $file;
            }
        }
        return $result;
    }

    /**
     * Get module directory by directory type
     *
     * @param Mage_Core_Model_ConfigInterface $modulesConfig
     * @param string $type
     * @param string $moduleName
     * @return string
     */
    public function getModuleDir(Mage_Core_Model_ConfigInterface $modulesConfig, $type, $moduleName)
    {
        if (isset($this->_moduleDirs[$moduleName][$type])) {
            return $this->_moduleDirs[$moduleName][$type];
        }

        $codePool = (string)$modulesConfig->getNode('modules/' . $moduleName . '/codePool');

        $dir = $this->_dirs->getDir(Mage_Core_Model_Dir::MODULES) . DIRECTORY_SEPARATOR
            . $codePool . DIRECTORY_SEPARATOR
            . uc_words($moduleName, DIRECTORY_SEPARATOR);

        switch ($type) {
            case 'etc':
            case 'controllers':
            case 'sql':
            case 'data':
            case 'locale':
            case 'view':
                $dir .= DS . $type;
                break;
        }

        $dir = str_replace('/', DS, $dir);
        return $dir;
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
        if (!isset($this->_moduleDirs[$moduleName])) {
            $this->_moduleDirs[$moduleName] = array();
        }
        $this->_moduleDirs[$moduleName][$type] = $path;
    }
}
