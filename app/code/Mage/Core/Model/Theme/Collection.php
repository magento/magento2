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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme filesystem collection
 */
class Mage_Core_Model_Theme_Collection extends Varien_Data_Collection
{
    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * Model of collection item
     *
     * @var string
     */
    protected $_itemObjectClass = 'Mage_Core_Model_Theme';

    /**
     * Base directory with design
     *
     * @var string
     */
    protected $_baseDir;

    /**
     * Target directory
     *
     * @var array
     */
    protected $_targetDirs = array();

    /**
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_Dir $dirs
     */
    public function __construct(
        Magento_Filesystem $filesystem,
        Mage_Core_Model_Dir $dirs
    ) {
        parent::__construct();
        $this->_filesystem = $filesystem;
        $this->setBaseDir($dirs->getDir(Mage_Core_Model_Dir::THEMES));
    }

    /**
     * Set base directory path of design
     *
     * @param string $path
     * @return Mage_Core_Model_Theme_Collection
     */
    public function setBaseDir($path)
    {
        if ($this->isLoaded() && $this->_baseDir) {
            $this->clearTargetPatterns()->clear();
        }
        $this->_baseDir = rtrim($path, DIRECTORY_SEPARATOR);
        return $this;
    }

    /**
     * Get base directory path
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->_baseDir;
    }

    /**
     * Add default pattern to themes configuration
     *
     * @param string $area
     * @return Mage_Core_Model_Theme_Collection
     */
    public function addDefaultPattern($area = Mage_Core_Model_App_Area::AREA_FRONTEND)
    {
        $this->addTargetPattern(implode(DIRECTORY_SEPARATOR, array($area, '*', '*', 'theme.xml')));
        return $this;
    }

    /**
     * Target directory setter. Adds directory to be scanned
     *
     * @param string $relativeTarget
     * @return Mage_Core_Model_Theme_Collection
     */
    public function addTargetPattern($relativeTarget)
    {
        if ($this->isLoaded()) {
            $this->clear();
        }
        $this->_targetDirs[] = $relativeTarget;
        return $this;
    }

    /**
     * Clear target patterns
     *
     * @return Mage_Core_Model_Theme_Collection
     */
    public function clearTargetPatterns()
    {
        $this->_targetDirs = array();
        return $this;
    }

    /**
     * Return target dir for themes with theme configuration file
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
            $themeConfigs = $this->_filesystem->searchKeys($this->getBaseDir(), $directoryPath);
            $themeConfigs = str_replace('/', DIRECTORY_SEPARATOR, $themeConfigs);
            $pathsToThemeConfig = array_merge($pathsToThemeConfig, $themeConfigs);
        }

        $this->_loadFromFilesystem($pathsToThemeConfig)
            ->clearTargetPatterns()
            ->_updateRelations()
            ->_renderFilters()
            ->_clearFilters();

        return $this;
    }

    /**
     * Set all parent themes
     *
     * @return Mage_Core_Model_Theme_Collection
     */
    protected function _updateRelations()
    {
        $themeItems = $this->getItems();
        /** @var $theme Varien_Object|Mage_Core_Model_ThemeInterface */
        foreach ($themeItems as $theme) {
            $parentThemePath = $theme->getParentThemePath();
            if ($parentThemePath) {
                $themePath = $theme->getArea() . Mage_Core_Model_ThemeInterface::PATH_SEPARATOR . $parentThemePath;
                if (isset($themeItems[$themePath])) {
                    $theme->setParentTheme($themeItems[$themePath]);
                }
            }
        }
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
            $theme = $this->getNewEmptyItem()
                ->addData($this->_preparePathData($themeConfigPath))
                ->addData($this->_prepareConfigurationData($themeConfigPath));
            $this->addItem($theme);
        }
        $this->_setIsLoaded();

        return $this;
    }

    /**
     * Return default path related data
     *
     * @param string $configPath
     * @return array
     */
    protected function _preparePathData($configPath)
    {
        $themeDirectory = dirname($configPath);
        $fullPath = trim(substr($themeDirectory, strlen($this->getBaseDir())), DIRECTORY_SEPARATOR);
        $pathPieces = explode(DIRECTORY_SEPARATOR, $fullPath);
        $area = array_shift($pathPieces);
        $themePath = implode(Mage_Core_Model_Theme::PATH_SEPARATOR, $pathPieces);
        return array('area' => $area, 'theme_path' => $themePath, 'theme_directory' => $themeDirectory);
    }

    /**
     * Return default configuration data
     *
     * @param string $configPath
     * @return array
     */
    public function _prepareConfigurationData($configPath)
    {
        $themeConfig = $this->_getConfigModel(array($configPath));

        $packageCodes = $themeConfig->getPackageCodes();
        $packageCode = reset($packageCodes);

        $themeCodes = $themeConfig->getPackageThemeCodes($packageCode);
        $themeCode = reset($themeCodes);

        $themeVersions = $themeConfig->getCompatibleVersions($packageCode, $themeCode);
        $media = $themeConfig->getMedia($packageCode, $themeCode);
        $parentTheme = $themeConfig->getParentTheme($packageCode, $themeCode);

        return array(
            'code'                 => $packageCode . Mage_Core_Model_Theme::CODE_SEPARATOR . $themeCode,
            'parent_id'            => null,
            'type'                 => Mage_Core_Model_Theme::TYPE_PHYSICAL,
            'theme_version'        => $themeConfig->getThemeVersion($packageCode, $themeCode),
            'theme_title'          => $themeConfig->getThemeTitle($packageCode, $themeCode),
            'preview_image'        => $media['preview_image'] ? $media['preview_image'] : null,
            'magento_version_from' => $themeVersions['from'],
            'magento_version_to'   => $themeVersions['to'],
            'is_featured'          => $themeConfig->isFeatured($packageCode, $themeCode),
            'parent_theme_path'    => $parentTheme ? implode('/', $parentTheme) : null
        );
    }

    /**
     * Apply set field filters
     *
     * @return Mage_Core_Model_Theme_Collection
     */
    protected function _renderFilters()
    {
        $filters = $this->getFilter(array());
        /** @var $theme Mage_Core_Model_Theme */
        foreach ($this->getItems() as $itemKey => $theme) {
            $removeItem = false;
            foreach ($filters as $filter) {
                if ($filter['type'] == 'and' && $theme->getDataUsingMethod($filter['field']) != $filter['value']) {
                    $removeItem = true;
                }
            }
            if ($removeItem) {
                $this->removeItemByKey($itemKey);
            }
        }
        return $this;
    }

    /**
     * Clear all added filters
     *
     * @return Mage_Core_Model_Theme_Collection
     */
    protected function _clearFilters()
    {
        $this->_filters = array();
        return $this;
    }

    /**
     * Return configuration model for themes
     *
     * @param array $configPaths
     * @return Magento_Config_Theme
     */
    protected function _getConfigModel(array $configPaths)
    {
        return new Magento_Config_Theme($configPaths);
    }

    /**
     * Retrieve item id
     *
     * @param Mage_Core_Model_Theme|Varien_Object $item
     * @return string
     */
    protected function _getItemId(Varien_Object $item)
    {
        return $item->getFullPath();
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
     * Checks that a theme present in filesystem collection
     *
     * @param Mage_Core_Model_ThemeInterface $theme
     * @return bool
     */
    public function hasTheme(Mage_Core_Model_ThemeInterface $theme)
    {
        $themeItems = $this->getItems();
        return $theme->getThemePath() && isset($themeItems[$theme->getFullPath()]);
    }
}
