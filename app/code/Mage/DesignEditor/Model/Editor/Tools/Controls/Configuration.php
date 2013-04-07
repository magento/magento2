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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Configuration of controls
 */
class Mage_DesignEditor_Model_Editor_Tools_Controls_Configuration
{
    /**
     * Module name used for saving data to the view configuration
     */
    const SEPARATOR_MODULE = '::';

    /**
     * Application Event Dispatcher
     *
     * @var Mage_Core_Model_Event_Manager
     */
    protected $_eventDispatcher;

    /**
     * @var Mage_DesignEditor_Model_Config_Control_Abstract
     */
    protected $_configuration;

    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_design;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @var Mage_Core_Model_Theme
     */
    protected $_theme;

    /**
     * @var Magento_Config_View
     */
    protected $_viewConfig;

    /**
     * @var Magento_Config_View
     */
    protected $_viewConfigParent;

    /**
     * Controls data
     *
     * @var array
     */
    protected $_data;

    /**
     * List of controls
     *
     * @var array
     */
    protected $_controlList = array();

    /**
     * Initialize dependencies
     *
     * @param Mage_Core_Model_Design_Package $design
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_Event_Manager $eventDispatcher
     * @param Mage_DesignEditor_Model_Config_Control_Abstract|null $configuration
     * @param Mage_Core_Model_Theme|null $theme
     */
    public function __construct(
        Mage_Core_Model_Design_Package $design,
        Magento_Filesystem $filesystem,
        Mage_Core_Model_Event_Manager $eventDispatcher,
        Mage_DesignEditor_Model_Config_Control_Abstract $configuration = null,
        Mage_Core_Model_Theme $theme = null
    ) {
        $this->_configuration = $configuration;
        $this->_theme = $theme;
        $this->_design = $design;
        $this->_filesystem = $filesystem;
        $this->_eventDispatcher = $eventDispatcher;
        $this->_initViewConfigs()->_loadControlsData();
    }

    /**
     * Initialize view configurations
     *
     * @return Mage_DesignEditor_Model_Editor_Tools_Controls_Configuration
     */
    protected function _initViewConfigs()
    {
        $this->_viewConfig = $this->_design->getViewConfig(array(
            'area'       => Mage_Core_Model_Design_Package::DEFAULT_AREA,
            'themeModel' => $this->_theme
        ));
        $this->_viewConfigParent = $this->_design->getViewConfig(array(
            'area'       => Mage_Core_Model_Design_Package::DEFAULT_AREA,
            'themeModel' => $this->_theme->getParentTheme()
        ));
        return $this;
    }

    /**
     * Load all control values
     *
     * @return Mage_DesignEditor_Model_Editor_Tools_Controls_Configuration
     */
    protected function _loadControlsData()
    {
        $this->_data = $this->_configuration->getAllControlsData();
        $this->_prepareControlList($this->_data);
        foreach ($this->_controlList as &$control) {
            $this->_loadControlData($control, 'value', $this->_viewConfig);
            $this->_loadControlData($control, 'default', $this->_viewConfigParent);
        }
        return $this;
    }

    /**
     * Prepare list of control links
     *
     * @param array $controls
     * @return Mage_DesignEditor_Model_Editor_Tools_Controls_Configuration
     */
    protected function _prepareControlList(array &$controls)
    {
        foreach ($controls as $controlName => &$control) {
            if (!empty($control['components'])) {
                $this->_prepareControlList($control['components']);
            }
            $this->_controlList[$controlName] = &$control;
        }
        return $this;
    }

    /**
     * Load data item values and default values from the view configuration
     *
     * @param array $control
     * @param string $paramName
     * @param Magento_Config_View $viewConfiguration
     * @return Mage_DesignEditor_Model_Editor_Tools_Controls_Configuration
     */
    protected function _loadControlData(array &$control, $paramName, Magento_Config_View $viewConfiguration)
    {
        if (!empty($control['var'])) {
            list($module, $varKey) = $this->_extractModuleKey($control['var']);
            $control[$paramName] = $viewConfiguration->getVarValue($module, $varKey);
        }
        return $this;
    }

    /**
     * Load control data
     *
     * @return array
     */
    public function getAllControlsData()
    {
        return $this->_data;
    }

    /**
     * Get control data
     *
     * @param string $controlName
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getControlData($controlName)
    {
        if (!isset($this->_controlList[$controlName])) {
            throw new Mage_Core_Exception("Unknown control: \"{$controlName}\"");
        }
        return $this->_controlList[$controlName];
    }

    /**
     * Extract module and key name
     *
     * @param string $value
     * @return array
     */
    protected function _extractModuleKey($value)
    {
        return explode(self::SEPARATOR_MODULE, $value);
    }

    /**
     * Extract var data keys for current controls configuration
     * array(module => array(varKey => array(controlName, controlValue)))
     *
     * @param array $controlsData
     * @param array $controls
     * @return array
     */
    protected function _prepareVarData(array $controlsData, array $controls)
    {
        $result = array();
        foreach ($controlsData as $controlName => $controlValue) {
            if (isset($controls[$controlName])) {
                list($module, $varKey) = $this->_extractModuleKey($controls[$controlName]['var']);
                $result[$module][$varKey] = array($controlName, $controlValue);
            }
        }
        return $result;
    }

    /**
     * Save control values data
     *
     * @param array $controlsData
     * @return Mage_DesignEditor_Model_Editor_Tools_Controls_Configuration
     */
    public function saveData(array $controlsData)
    {
        $configDom = $this->_viewConfig->getDomConfigCopy()->getDom();
        $varData = $this->_prepareVarData($controlsData, $this->_controlList);

        /** @var $varsNode DOMElement */
        foreach ($configDom->childNodes->item(0)->childNodes as $varsNode) {
            $moduleName = $varsNode->getAttribute('module');
            if (!isset($varData[$moduleName])) {
                continue;
            }
            /** @var $varNode DOMElement */
            foreach ($varsNode->getElementsByTagName('var') as $varNode) {
                $varName = $varNode->getAttribute('name');
                if (isset($varData[$moduleName][$varName])) {
                    list($controlName, $controlValue) = $varData[$moduleName][$varName];
                    $varNode->nodeValue = $controlValue;
                    $this->_controlList[$controlName]['value'] = $controlValue;
                }
            }
        }
        $this->_saveViewConfiguration($configDom);
        $this->_eventDispatcher->dispatch('save_xml_configuration', array('configuration' => $this));
        return $this;
    }

    /**
     * Get control configuration
     *
     * @return Mage_DesignEditor_Model_Config_Control_Abstract
     */
    public function getControlConfig()
    {
        return $this->_configuration;
    }

    /**
     * Get theme
     *
     * @return Mage_Core_Model_Theme
     */
    public function getTheme()
    {
        return $this->_theme;
    }

    /**
     * Save customized DOM of view configuration
     *
     * @param DOMDocument $config
     * @return Mage_DesignEditor_Model_Editor_Tools_Controls_Configuration
     */
    protected function _saveViewConfiguration(DOMDocument $config)
    {
        $targetPath = $this->_theme->getCustomViewConfigPath();
        $this->_filesystem->setIsAllowCreateDirectories(true)->write($targetPath, $config->saveXML());
        return $this;
    }
}
