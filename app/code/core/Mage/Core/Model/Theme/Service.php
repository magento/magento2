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
 * Theme Service model
 */
class Mage_Core_Model_Theme_Service
{
    /**
     * @var Mage_Core_Model_Theme_Factory
     */
    protected $_themeFactory;

    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_design;

    /**
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * Flag that shows if theme customizations exist in Magento
     *
     * @var bool
     */
    protected $_isCustomizationsExist;

    /**
     * Theme customizations which are assigned to store views or as default
     *
     * @see self::_prepareThemeCustomizations()
     * @var array
     */
    protected $_assignedThemeCustomizations;

    /**
     * Theme customizations which are not assigned to store views or as default
     *
     * @see self::_prepareThemeCustomizations()
     * @var array
     */
    protected $_unassignedThemeCustomizations;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_helper;

    /**
     * Initialize service model
     *
     * @param Mage_Core_Model_Theme_Factory $themeFactory
     * @param Mage_Core_Model_Design_Package $design
     * @param Mage_Core_Model_App $app
     * @param Mage_Core_Helper_Data $helper
     */
    public function __construct(
        Mage_Core_Model_Theme_Factory $themeFactory,
        Mage_Core_Model_Design_Package $design,
        Mage_Core_Model_App $app,
        Mage_Core_Helper_Data $helper
    ) {
        $this->_themeFactory = $themeFactory;
        $this->_design = $design;
        $this->_app = $app;
        $this->_helper = $helper;
    }

    /**
     * Assign theme to the stores
     *
     * @param int $themeId
     * @param array|null $stores
     * @param string $scope
     * @param string $area
     * @return Mage_Core_Model_Theme_Service
     * @throws UnexpectedValueException
     */
    public function assignThemeToStores($themeId, $stores, $scope = Mage_Core_Model_Config::SCOPE_STORES,
        $area = Mage_Core_Model_App_Area::AREA_FRONTEND
    ) {
        /** @var $theme Mage_Core_Model_Theme */
        $theme = $this->_themeFactory->create()->load($themeId);
        if (!$theme->getId()) {
            throw new UnexpectedValueException('Theme is not recognized. Requested id: ' . $themeId);
        }

        $themeCustomization = $theme->isVirtual() ? $theme : $this->_createThemeCustomization($theme);

        $configPath = $this->_design->getConfigPathByArea($area);

        foreach ($this->_getAssignedScopesCollection($scope, $configPath) as $config) {
            if ($config->getValue() == $themeId && !in_array($config->getScopeId(), $stores)) {
                $this->_app->getConfig()->deleteConfig($configPath, $scope, $config->getScopeId());
            }
        }

        foreach ($stores as $storeId) {
            $this->_app->getConfig()->saveConfig($configPath, $themeCustomization->getId(), $scope, $storeId);
        }

        if ($stores === null || count($stores) > 0) {
            $this->_app->cleanCache(Mage_Core_Model_Config::CACHE_TAG);
        }

        return $this;
    }

    /**
     * Create theme customization
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Theme
     */
    protected function _createThemeCustomization($theme)
    {
        $themeCopyCount = $this->_getThemeCustomizations()->addFilter('parent_id', $theme->getId())->count();

        $themeData = $theme->getData();
        $themeData['parent_id'] = $theme->getId();
        $themeData['theme_id'] = null;
        $themeData['theme_path'] = null;
        $themeData['theme_title'] = $theme->getThemeTitle() . ' - ' . $this->_helper->__('Copy') . ' #'
            . ($themeCopyCount + 1);

        /** @var $themeCustomization Mage_Core_Model_Theme */
        $themeCustomization = $this->_themeFactory->create()->setData($themeData);
        $themeCustomization->createPreviewImageCopy()->save();
        return $themeCustomization;
    }

    /**
     * Get assigned scopes collection of a theme
     *
     * @param string $scope
     * @param string $configPath
     * @return Mage_Core_Model_Resource_Config_Data_Collection
     */
    protected function _getAssignedScopesCollection($scope, $configPath)
    {
        return $this->_app->getConfig()->getConfigDataModel()->getCollection()
            ->addFieldToFilter('scope', $scope)
            ->addFieldToFilter('path', $configPath);
    }

    /**
     * Check whether theme customizations exist in Magento
     *
     * @return bool
     */
    public function isCustomizationsExist()
    {
        if ($this->_isCustomizationsExist === null) {
            $this->_isCustomizationsExist = false;
            /** @var $theme Mage_Core_Model_Theme */
            foreach ($this->_themeFactory->create()->getCollection() as $theme) {
                if ($theme->isVirtual()) {
                    $this->_isCustomizationsExist = true;
                    break;
                }
            }
        }
        return $this->_isCustomizationsExist;
    }

    /**
     * Return frontend theme collection by page. Theme customizations are not included, only phisical themes.
     *
     * @param int $page
     * @param int $pageSize
     * @return Mage_Core_Model_Resource_Theme_Collection
     */
    public function getThemes($page, $pageSize)
    {
        /** @var $collection Mage_Core_Model_Resource_Theme_Collection */
        $collection = $this->_themeFactory->create()->getCollection();
        $collection->addAreaFilter(Mage_Core_Model_App_Area::AREA_FRONTEND)
            ->addFilter('theme_path', 'theme_path IS NOT NULL', 'string')
            ->setPageSize($pageSize);
        return $collection->setCurPage($page);
    }

    /**
     * Return theme customizations which are assigned to store views
     *
     * @see self::_prepareThemeCustomizations()
     * @return array
     */
    public function getAssignedThemeCustomizations()
    {
        if (is_null($this->_assignedThemeCustomizations)) {
            $this->_prepareThemeCustomizations();
        }
        return $this->_assignedThemeCustomizations;
    }

    /**
     * Return theme customizations which are not assigned to store views.
     *
     * @see self::_prepareThemeCustomizations()
     * @return array
     */
    public function getUnassignedThemeCustomizations()
    {
        if (is_null($this->_unassignedThemeCustomizations)) {
            $this->_prepareThemeCustomizations();
        }
        return $this->_unassignedThemeCustomizations;
    }

    /**
     * Fetch theme customization and sort them out to arrays:
     * self::_assignedThemeCustomizations and self::_unassignedThemeCustomizations.
     *
     * NOTE: To get into "assigned" list theme customization not necessary should be assigned to store-view directly.
     * It can be set to website or as default theme and be used by store-view via config fallback mechanism.
     *
     * @return Mage_Core_Model_Theme_Service
     */
    protected function _prepareThemeCustomizations()
    {
        /** @var $themeCustomizations Mage_Core_Model_Resource_Theme_Collection */
        $themeCustomizations = $this->_getThemeCustomizations();
        $assignedThemes = $this->getStoresByThemes();

        $this->_assignedThemeCustomizations = array();
        $this->_unassignedThemeCustomizations = array();
        /** @var $theme Mage_Core_Model_Theme */
        foreach ($themeCustomizations as $theme) {
            if (isset($assignedThemes[$theme->getId()])) {
                $theme->setAssignedStores($assignedThemes[$theme->getId()]);
                $this->_assignedThemeCustomizations[] = $theme;
            } else {
                $this->_unassignedThemeCustomizations[] = $theme;
            }
        }
        return $this;
    }

    /**
     * Return theme customizations collection
     *
     * @return Mage_Core_Model_Resource_Theme_Collection
     */
    protected function _getThemeCustomizations()
    {
        /** @var $collection Mage_Core_Model_Resource_Theme_Collection */
        $collection = $this->_themeFactory->create()->getCollection();
        $collection->addAreaFilter(Mage_Core_Model_App_Area::AREA_FRONTEND)
            ->addFilter('theme_path', 'theme_path IS NULL', 'string');
        return $collection;
    }

    /**
     * Return stores grouped by assigned themes
     *
     * @return array
     */
    public function getStoresByThemes()
    {
        $storesByThemes = array();
        $stores = $this->_app->getStores();
        /** @var $store Mage_Core_Model_Store */
        foreach ($stores as $store) {
            $themeId = $this->_design->getConfigurationDesignTheme(
                Mage_Core_Model_App_Area::AREA_FRONTEND,
                array('store' => $store)
            );
            if (!isset($storesByThemes[$themeId])) {
                $storesByThemes[$themeId] = array();
            }
            $storesByThemes[$themeId][] = $store;
        }

        return $storesByThemes;
    }
}
