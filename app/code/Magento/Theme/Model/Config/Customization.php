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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Theme\Model\Config;

/**
 * Theme customization config model
 */
class Customization
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemeProviderInterface
     */
    protected $themeProvider;

    /**
     * Theme customizations which are assigned to store views or as default
     *
     * @var array
     * @see self::_prepareThemeCustomizations()
     */
    protected $_assignedTheme;

    /**
     * Theme customizations which are not assigned to store views or as default
     *
     * @var array
     * @see self::_prepareThemeCustomizations()
     */
    protected $_unassignedTheme;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider
     */
    public function __construct(
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider
    ) {
        $this->_storeManager = $storeManager;
        $this->_design = $design;
        $this->themeProvider = $themeProvider;
    }

    /**
     * Return theme customizations which are assigned to store views
     *
     * @see self::_prepareThemeCustomizations()
     * @return array
     */
    public function getAssignedThemeCustomizations()
    {
        if (is_null($this->_assignedTheme)) {
            $this->_prepareThemeCustomizations();
        }
        return $this->_assignedTheme;
    }

    /**
     * Return theme customizations which are not assigned to store views.
     *
     * @see self::_prepareThemeCustomizations()
     * @return array
     */
    public function getUnassignedThemeCustomizations()
    {
        if (is_null($this->_unassignedTheme)) {
            $this->_prepareThemeCustomizations();
        }
        return $this->_unassignedTheme;
    }

    /**
     * Return stores grouped by assigned themes
     *
     * @return array
     */
    public function getStoresByThemes()
    {
        $storesByThemes = array();
        $stores = $this->_storeManager->getStores();
        /** @var $store \Magento\Store\Model\Store */
        foreach ($stores as $store) {
            $themeId = $this->_getConfigurationThemeId($store);
            if (!isset($storesByThemes[$themeId])) {
                $storesByThemes[$themeId] = array();
            }
            $storesByThemes[$themeId][] = $store;
        }
        return $storesByThemes;
    }

    /**
     * Check if current theme has assigned to any store
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param null|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isThemeAssignedToStore($theme, $store = null)
    {
        if (null === $store) {
            $assignedThemes = $this->getAssignedThemeCustomizations();
            return isset($assignedThemes[$theme->getId()]);
        }
        return $this->_isThemeAssignedToSpecificStore($theme, $store);
    }

    /**
     * Check if there are any themes assigned
     *
     * @return bool
     */
    public function hasThemeAssigned()
    {
        return count($this->getAssignedThemeCustomizations()) > 0;
    }

    /**
     * Is theme assigned to specific store
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param \Magento\Store\Model\Store $store
     * @return bool
     */
    protected function _isThemeAssignedToSpecificStore($theme, $store)
    {
        return $theme->getId() == $this->_getConfigurationThemeId($store);
    }

    /**
     * Get configuration theme id
     *
     * @param \Magento\Store\Model\Store $store
     * @return int
     */
    protected function _getConfigurationThemeId($store)
    {
        return $this->_design->getConfigurationDesignTheme(
            \Magento\Framework\App\Area::AREA_FRONTEND,
            array('store' => $store)
        );
    }

    /**
     * Fetch theme customization and sort them out to arrays:
     * self::_assignedTheme and self::_unassignedTheme.
     *
     * NOTE: To get into "assigned" list theme customization not necessary should be assigned to store-view directly.
     * It can be set to website or as default theme and be used by store-view via config fallback mechanism.
     *
     * @return $this
     */
    protected function _prepareThemeCustomizations()
    {
        /** @var \Magento\Core\Model\Resource\Theme\Collection $themeCollection */
        $themeCollection = $this->themeProvider->getThemeCustomizations(\Magento\Framework\App\Area::AREA_FRONTEND);

        $assignedThemes = $this->getStoresByThemes();

        $this->_assignedTheme = array();
        $this->_unassignedTheme = array();

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        foreach ($themeCollection as $theme) {
            if (isset($assignedThemes[$theme->getId()])) {
                $theme->setAssignedStores($assignedThemes[$theme->getId()]);
                $this->_assignedTheme[$theme->getId()] = $theme;
            } else {
                $this->_unassignedTheme[$theme->getId()] = $theme;
            }
        }

        return $this;
    }
}
