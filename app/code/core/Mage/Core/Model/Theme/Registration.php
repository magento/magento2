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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme registration model class
 */
class Mage_Core_Model_Theme_Registration
{
    /**
     * Collection of themes in file-system
     *
     * @var Mage_Core_Model_Theme_Collection
     */
    protected $_collection;

    /**
     * @var Mage_Core_Model_Theme
     */
    protected $_theme;

    /**
     * Init theme model
     *
     * @param Mage_Core_Model_Theme $model
     */
    public function __construct(Mage_Core_Model_Theme $model)
    {
        $this->setThemeModel($model);
    }

    /**
     * Get theme model
     *
     * @return Mage_Core_Model_Theme
     */
    public function getThemeModel()
    {
        return $this->_theme;
    }

    /**
     * Set theme model
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Theme_Registration
     */
    public function setThemeModel($theme)
    {
        $this->_theme = $theme;
        return $this;
    }

    /**
     * Theme registration
     *
     * @param string $baseDir
     * @param string $pathPattern
     * @return Mage_Core_Model_Theme
     */
    public function register($baseDir = '', $pathPattern = '')
    {
        $this->_collection = $this->getThemeModel()->getCollectionFromFilesystem();
        $this->_collection->setBaseDir($baseDir);
        if (empty($pathPattern)) {
            $this->_collection->addDefaultPattern('*');
        } else {
            $this->_collection->addTargetPattern($pathPattern);
        }

        foreach ($this->_collection as $theme) {
            $this->_registerThemeRecursively($theme);
        }

        $this->registerDefaultThemes();

        /** @var $dbCollection Mage_Core_Model_Resource_Theme_Collection */
        $dbCollection = $this->getThemeModel()->getResourceCollection();
        $dbCollection->checkParentInThemes();

        return $this;
    }

    /**
     * Register theme and recursively all its ascendants
     * Second param is optional and is used to prevent circular references in inheritance chain
     *
     * @param Mage_Core_Model_Theme $theme
     * @param array $inheritanceChain
     * @return Mage_Core_Model_Theme_Collection
     * @throws Mage_Core_Exception
     */
    protected function _registerThemeRecursively(&$theme, $inheritanceChain = array())
    {
        if ($theme->getId()) {
            return $this;
        }
        $themeModel = $this->getThemeFromDb($theme->getFullPath());
        if ($themeModel->getId()) {
            $theme = $themeModel;
            return $this;
        }

        $tempId = $theme->getFullPath();
        if (in_array($tempId, $inheritanceChain)) {
            Mage::throwException(Mage::helper('Mage_Core_Helper_Data')
                ->__('Circular-reference in theme inheritance detected for "%s"', $tempId));
        }
        array_push($inheritanceChain, $tempId);
        $parentTheme = $theme->getParentTheme();
        if ($parentTheme) {
            $this->_registerThemeRecursively($parentTheme, $inheritanceChain);
            $theme->setParentId($parentTheme->getId());
        }

        $theme->savePreviewImage()->save();
        return $this;
    }

    /**
     * Get default theme design paths specified in configuration
     *
     * @return array
     */
    protected function _getDefaultThemes()
    {
        $themesByArea = array();
        $themeItems = $this->_collection->getItems();
        /** @var $theme Mage_Core_Model_Theme */
        foreach ($themeItems as $theme) {
            $area = $theme->getArea();
            if (!isset($themesByArea[$area])) {
                $themePath = $this->_getDesign()->getConfigurationDesignTheme($area, array('useId' => false));
                $fullPath = $area . '/' . $themePath;
                $themesByArea[$area] = isset($themeItems[$fullPath]) ? $themeItems[$fullPath] : null;
            }
        }
        return $themesByArea;
    }

    /**
     * Set default themes stored in configuration
     *
     * @return Mage_Core_Model_Theme_Registration
     */
    public function registerDefaultThemes()
    {
        /** @var $theme Mage_Core_Model_Theme */
        foreach ($this->_getDefaultThemes() as $area => $theme) {
            if ($theme && $theme->getId()) {
                Mage::app()->getConfig()->saveConfig($this->_getDesign()->getConfigPathByArea($area), $theme->getId());
            }
        }
        return $this;
    }

    /**
     * Get current design model
     *
     * @return Mage_Core_Model_Design_Package
     */
    protected function _getDesign()
    {
        return Mage::getDesign();
    }

    /**
     * Get theme from DB by full path
     *
     * @param string $fullPath
     * @return Mage_Core_Model_Theme
     */
    public function getThemeFromDb($fullPath)
    {
        /** @var $collection Mage_Core_Model_Resource_Theme_Collection */
        $collection = $this->getThemeModel()->getCollection();
        return $collection->getThemeByFullPath($fullPath);
    }
}
