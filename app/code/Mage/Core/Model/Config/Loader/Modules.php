<?php
/**
 * Module configuration loader
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
class Mage_Core_Model_Config_Loader_Modules implements Mage_Core_Model_Config_LoaderInterface
{
    /**
     * Primary application configuration
     *
     * @var Mage_Core_Model_Config_Primary
     */
    protected $_primaryConfig;

    /**
     * Load modules configuration
     *
     * @var Mage_Core_Model_Dir
     */
    protected $_dirs;

    /**
     * Prototype config factory
     *
     * @var Mage_Core_Model_Config_BaseFactory
     */
    protected $_prototypeFactory;

    /**
     * Loaded modules
     *
     * @var array
     */
    protected $_modulesCache = array();

    /**
     * List of modules that should be loaded
     *
     * @var array
     */
    protected $_allowedModules = array();

    /**
     * @var Mage_Core_Model_Config_Resource
     */
    protected $_resourceConfig;

    /**
     * @var Mage_Core_Model_Config_Loader_Modules_File
     */
    protected $_fileReader;

    /**
     * Application object manager
     *
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @param Mage_Core_Model_Config_Primary $primaryConfig
     * @param Mage_Core_Model_Dir $dirs
     * @param Mage_Core_Model_Config_BaseFactory $prototypeFactory
     * @param Mage_Core_Model_Config_Resource $resourceConfig
     * @param Mage_Core_Model_Config_Loader_Modules_File $fileReader
     * @param Magento_ObjectManager
     * @param array $allowedModules
     */
    public function __construct(
        Mage_Core_Model_Config_Primary $primaryConfig,
        Mage_Core_Model_Dir $dirs,
        Mage_Core_Model_Config_BaseFactory $prototypeFactory,
        Mage_Core_Model_Config_Resource $resourceConfig,
        Mage_Core_Model_Config_Loader_Modules_File $fileReader,
        Magento_ObjectManager $objectManager,
        array $allowedModules = array()
    ) {
        $this->_dirs = $dirs;
        $this->_primaryConfig = $primaryConfig;
        $this->_allowedModules = $allowedModules;
        $this->_prototypeFactory = $prototypeFactory;
        $this->_resourceConfig = $resourceConfig;
        $this->_fileReader = $fileReader;
        $this->_objectManager = $objectManager;
    }

    /**
     * Populate configuration object
     *
     * @param Mage_Core_Model_Config_Base $config
     */
    public function load(Mage_Core_Model_Config_Base $config)
    {
        if (!$config->getNode()) {
            $config->loadString('<config><modules></modules></config>');
        }

        Magento_Profiler::start('config');
        Magento_Profiler::start('load_modules');

        $config->extend($this->_primaryConfig);

        $this->_loadDeclaredModules($config);

        Magento_Profiler::start('load_modules_configuration');
        $resourceConfig = sprintf('config.%s.xml', $this->_resourceConfig->getResourceConnectionModel('core'));
        $this->_fileReader->loadConfigurationFromFile(
            $config, array('config.xml', $resourceConfig), $config, null, $this->_modulesCache
        );
        Magento_Profiler::stop('load_modules_configuration');

        // Prevent local configuration overriding
        $config->extend($this->_primaryConfig);

        $config->applyExtends();

        Magento_Profiler::stop('load_modules');
        Magento_Profiler::stop('config');
        $this->_resourceConfig->setConfig($config);
        $this->_objectManager->configure($config->getNode('global/di')->asArray());
        $this->_modulesCache = array();
    }

    /**
     * Load declared modules configuration
     *
     * @param Mage_Core_Model_Config_Base $mergeToConfig
     */
    protected function _loadDeclaredModules(Mage_Core_Model_Config_Base $mergeToConfig)
    {
        Magento_Profiler::start('load_modules_files');
        $moduleFiles = $this->_getDeclaredModuleFiles();
        if (!$moduleFiles) {
            return;
        }
        Magento_Profiler::stop('load_modules_files');

        Magento_Profiler::start('load_modules_declaration');
        $unsortedConfig = new Mage_Core_Model_Config_Base('<config/>');
        $emptyConfig = new Mage_Core_Model_Config_Element('<config><modules/></config>');
        $declaredModules = array();
        foreach ($moduleFiles as $oneConfigFile) {
            $path = explode(DIRECTORY_SEPARATOR, $oneConfigFile);
            $moduleConfig = new Mage_Core_Model_Config_Base($oneConfigFile);
            $modules = $moduleConfig->getXpath('modules/*');
            if (!$modules) {
                continue;
            }
            $cPath = count($path);
            if ($cPath > 4) {
                $moduleName = $path[$cPath - 4] . '_' . $path[$cPath - 3];
                $this->_modulesCache[$moduleName] = $moduleConfig;
            }
            foreach ($modules as $module) {
                $moduleName = $module->getName();
                $isActive = (string)$module->active;
                if (isset($declaredModules[$moduleName])) {
                    $declaredModules[$moduleName]['active'] = $isActive;
                    continue;
                }
                $newModule = clone $emptyConfig;
                $newModule->modules->appendChild($module);
                $declaredModules[$moduleName] = array(
                    'active' => $isActive,
                    'module' => $newModule,
                );
            }
        }
        foreach ($declaredModules as $moduleName => $module) {
            if ($module['active'] == 'true') {
                $module['module']->modules->{$moduleName}->active = 'true';
                $unsortedConfig->extend(new Mage_Core_Model_Config_Base($module['module']));
            }
        }
        $sortedConfig = new Mage_Core_Model_Config_Modules_Sorted($unsortedConfig, $this->_allowedModules);

        $mergeToConfig->extend($sortedConfig);
        Magento_Profiler::stop('load_modules_declaration');
    }

    /**
     * Retrieve Declared Module file list
     *
     * @return array
     */
    protected function _getDeclaredModuleFiles()
    {
        $codeDir = $this->_dirs->getDir(Mage_Core_Model_Dir::MODULES);
        $moduleFiles = glob($codeDir . DS . '*' . DS . '*' . DS . 'etc' . DS . 'config.xml');

        if (!$moduleFiles) {
            return false;
        }

        $collectModuleFiles = array(
            'base'   => array(),
            'mage'   => array(),
            'custom' => array()
        );

        foreach ($moduleFiles as $v) {
            $name = explode(DIRECTORY_SEPARATOR, $v);
            $collection = $name[count($name) - 4];

            if ($collection == 'Mage') {
                $collectModuleFiles['mage'][] = $v;
            } else {
                $collectModuleFiles['custom'][] = $v;
            }
        }

        $etcDir = $this->_dirs->getDir(Mage_Core_Model_Dir::CONFIG);
        $additionalFiles = glob($etcDir . DS . 'modules' . DS . '*.xml');

        foreach ($additionalFiles as $v) {
            $collectModuleFiles['base'][] = $v;
        }

        return array_merge(
            $collectModuleFiles['mage'],
            $collectModuleFiles['custom'],
            $collectModuleFiles['base']
        );
    }
}
