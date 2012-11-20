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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme filesystem collection
 */
class Mage_Core_Model_Theme_Collection extends Varien_Data_Collection
{
    /**
     * Model of collection item
     *
     * @var string
     */
    protected $_itemObjectClass = 'Mage_Core_Model_Theme';

    /**
     * Target directory
     *
     * @var array
     */
    protected $_targetDirs = array();

    /**
     * Theme list
     *
     * @var array
     */
    protected $_themeList = array();

    /**
     * Retrieve collection empty item
     *
     * @return Mage_Core_Model_Theme
     */
    public function getNewEmptyItem()
    {
        return Mage::getModel($this->_itemObjectClass);
    }

    /**
     * Add default pattern to themes configuration
     *
     * @param string $area
     * @return Mage_Core_Model_Theme_Collection
     */
    public function addDefaultPattern($area = 'frontend')
    {
        $this->addTargetPattern(implode(DS, array(Mage::getBaseDir('design'), $area, '*', '*', 'theme.xml')));
        return $this;
    }

    /**
     * Target directory setter. Adds directory to be scanned
     *
     * @throws Exception
     * @param string $value
     * @return Mage_Core_Model_Theme_Collection
     */
    public function addTargetPattern($value)
    {
        $this->_targetDirs[] = $value;
        return $this;
    }

    /**
     * Return target dir for themes with theme configuration file
     *
     *
     * @throws Magento_Exception
     * @return array|string
     */
    public function getTargetPatterns()
    {
        if (empty($this->_targetDirs)) {
            throw new Magento_Exception('Please specify at least one target pattern to theme config file.');
        }
        return $this->_targetDirs;
    }

    /**
     * Fill collection with theme model loaded from filesystem
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return Mage_Core_Model_Theme_Collection
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $pathsToThemeConfig = array();
        foreach ($this->getTargetPatterns() as $directoryPath) {
            $pathsToThemeConfig = array_merge($pathsToThemeConfig, glob($directoryPath, GLOB_NOSORT));
        }

        $this->_loadFromFilesystem($pathsToThemeConfig);
        return $this;
    }

    /**
     * Load themes collection from file system by file list
     *
     * @param array $themeConfigPaths
     * @return Mage_Core_Model_Theme_Collection
     */
    protected function _loadFromFilesystem(array $themeConfigPaths)
    {
        foreach ($themeConfigPaths as $themeConfigPath) {
            $theme = $this->getNewEmptyItem()->loadFromConfiguration($themeConfigPath);
            $this->addItem($theme);
        }
        $this->_setIsLoaded();
        return $this;
    }

    /**
     * Retrieve item id
     *
     * @param Mage_Core_Model_Theme|Varien_Object $item
     * @return string
     */
    protected function _getItemId(Varien_Object $item)
    {
        return $item->getThemePath();
    }

    /**
     * Get items array
     *
     * @return array
     */
    public function getItemsArray()
    {
        $items = array();
        /** @var $item Mage_Core_Model_Theme */
        foreach ($this as $item) {
            $items[$item->getThemeCode()] = $item->toArray();
        }
        return $items;
    }

    /**
     * Return array for select field
     *
     * @param bool $addEmptyField
     * @return array
     */
    public function toOptionArray($addEmptyField = false)
    {
        $optionArray = $addEmptyField ? array('' => '') : array();
        return $optionArray + $this->_toOptionArray('theme_id', 'theme_title');
    }

    /**
     * Register all themes in file system
     *
     * @return Mage_Core_Model_Theme_Collection
     */
    public function themeRegistration()
    {
        foreach ($this as $theme) {
            $this->_saveThemeRecursively($theme);
        }
        return $this;
    }

    /**
     * Save theme recursively
     *
     * @throws Mage_Core_Exception
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Theme_Collection
     */
    protected function _saveThemeRecursively($theme)
    {
        $themeModel = $this->_loadThemeByPath($theme->getThemePath());
        if ($themeModel->getId()) {
            return $this;
        }

        $this->_addThemeToList($theme->getThemePath());
        if ($theme->getParentTheme()) {
            $parentTheme = $this->_prepareParentTheme($theme);
            if (!$parentTheme->getId()) {
                Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Invalid parent theme path'));
            }
            $theme->setParentId($parentTheme->getId());
        }

        $theme->savePreviewImage()->save();
        $this->_emptyThemeList();
        return $this;
    }

    /**
     * Prepare parent theme
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Theme
     */
    protected function _prepareParentTheme($theme)
    {
        $parentThemePath = implode('/', $theme->getParentTheme());
        $themeModel = $this->_loadThemeByPath($parentThemePath);

        if (!$themeModel->getId()) {
            /**
             * Find theme model in file system collection
             */
            $filesystemThemeModel = $this->getItemByColumnValue('theme_path', $parentThemePath);
            if ($filesystemThemeModel !== null) {
                $this->_saveThemeRecursively($filesystemThemeModel);
                return $filesystemThemeModel;
            }
        }

        return $themeModel;
    }

    /**
     * Add theme path to list
     *
     * @throws Mage_Core_Exception
     * @param string $themePath
     * @return Mage_Core_Model_Theme_Collection
     */
    protected function _addThemeToList($themePath)
    {
        if (in_array($themePath, $this->_themeList)) {
            Mage::throwException(
                Mage::helper('Mage_Core_Helper_Data')->__('Invalid parent theme (сross-references) leads to an infinite loop.')
            );
        }
        array_push($this->_themeList, $themePath);
        return $this;
    }

    /**
     * Clear theme list
     *
     * @return Mage_Core_Model_Theme_Collection
     */
    protected function _emptyThemeList()
    {
        $this->_themeList = array();
        return $this;
    }

    /**
     * Load theme by path
     *
     * @param string  $themePath
     * @return Mage_Core_Model_Theme
     */
    protected function _loadThemeByPath($themePath)
    {
        return $this->getNewEmptyItem()->load($themePath, 'theme_path');
    }
}
