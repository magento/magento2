<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Editor\Tools\Controls;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Configuration of controls
 */
class Configuration
{
    /**
     * Module name used for saving data to the view configuration
     */
    const SEPARATOR_MODULE = '::';

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventDispatcher;

    /**
     * @var \Magento\DesignEditor\Model\Config\Control\AbstractControl
     */
    protected $_configuration;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Core\Model\Theme
     */
    protected $_theme;

    /**
     * @var \Magento\Core\Model\Theme
     */
    protected $_parentTheme;

    /**
     * @var \Magento\Framework\Config\View
     */
    protected $_viewConfig;

    /**
     * @var \Magento\Framework\Config\View
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
    protected $_controlList = [];

    /**
     * View config model
     *
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $_viewConfigLoader;

    /**
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Event\ManagerInterface $eventDispatcher
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \Magento\DesignEditor\Model\Config\Control\AbstractControl $configuration
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param \Magento\Framework\View\Design\ThemeInterface $parentTheme
     */
    public function __construct(
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Event\ManagerInterface $eventDispatcher,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\DesignEditor\Model\Config\Control\AbstractControl $configuration = null,
        \Magento\Framework\View\Design\ThemeInterface $theme = null,
        \Magento\Framework\View\Design\ThemeInterface $parentTheme = null
    ) {
        $this->_configuration = $configuration;
        $this->_theme = $theme;
        $this->_parentTheme = $parentTheme ?: $theme->getParentTheme();
        $this->_design = $design;
        $this->_filesystem = $filesystem;
        $this->_eventDispatcher = $eventDispatcher;
        $this->_viewConfigLoader = $viewConfig;
        $this->_initViewConfigs()->_loadControlsData();
    }

    /**
     * Initialize view configurations
     *
     * @return $this
     */
    protected function _initViewConfigs()
    {
        $this->_viewConfig = $this->_viewConfigLoader->getViewConfig(
            ['area' => \Magento\Framework\View\DesignInterface::DEFAULT_AREA, 'themeModel' => $this->_theme]
        );
        $this->_viewConfigParent = $this->_viewConfigLoader->getViewConfig(
            ['area' => \Magento\Framework\View\DesignInterface::DEFAULT_AREA, 'themeModel' => $this->_parentTheme]
        );
        return $this;
    }

    /**
     * Load all control values
     *
     * @return $this
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
     * @param array &$controls
     * @return $this
     */
    protected function _prepareControlList(array &$controls)
    {
        foreach ($controls as $controlName => &$control) {
            if (!empty($control['components'])) {
                $this->_prepareControlList($control['components']);
            }
            $this->_controlList[$controlName] = & $control;
        }
        return $this;
    }

    /**
     * Load data item values and default values from the view configuration
     *
     * @param array &$control
     * @param string $paramName
     * @param \Magento\Framework\Config\View $viewConfiguration
     * @return $this
     */
    protected function _loadControlData(array &$control, $paramName, \Magento\Framework\Config\View $viewConfiguration)
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
     * @throws \Magento\Framework\Model\Exception
     */
    public function getControlData($controlName)
    {
        if (!isset($this->_controlList[$controlName])) {
            throw new \Magento\Framework\Model\Exception("Unknown control: \"{$controlName}\"");
        }
        return $this->_controlList[$controlName];
    }

    /**
     * Extract module and key name
     *
     * @param string $value
     * @return string[]
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
        $result = [];
        foreach ($controlsData as $controlName => $controlValue) {
            if (isset($controls[$controlName])) {
                list($module, $varKey) = $this->_extractModuleKey($controls[$controlName]['var']);
                $result[$module][$varKey] = [$controlName, $controlValue];
            }
        }
        return $result;
    }

    /**
     * Save control values data
     *
     * @param array $controlsData
     * @return $this
     */
    public function saveData(array $controlsData)
    {
        $configDom = $this->_viewConfig->getDomConfigCopy()->getDom();
        $varData = $this->_prepareVarData($controlsData, $this->_controlList);

        /** @var $varsNode \DOMElement */
        foreach ($configDom->childNodes->item(0)->childNodes as $varsNode) {
            $moduleName = $varsNode->getAttribute('module');
            if (!isset($varData[$moduleName])) {
                continue;
            }
            /** @var $varNode \DOMElement */
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
        $this->_eventDispatcher->dispatch(
            'save_view_configuration',
            ['configuration' => $this, 'theme' => $this->_theme]
        );
        return $this;
    }

    /**
     * Get control configuration
     *
     * @return \Magento\DesignEditor\Model\Config\Control\AbstractControl
     */
    public function getControlConfig()
    {
        return $this->_configuration;
    }

    /**
     * Save customized DOM of view configuration
     *
     * @param \DOMDocument $config
     * @return $this
     */
    protected function _saveViewConfiguration(\DOMDocument $config)
    {
        $targetPath = $this->_theme->getCustomization()->getCustomViewConfigPath();
        $directory = $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $directory->writeFile($directory->getRelativePath($targetPath), $config->saveXML());
        return $this;
    }
}
