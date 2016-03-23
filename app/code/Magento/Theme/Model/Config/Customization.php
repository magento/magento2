<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Config;

/**
 * Theme customization config model
 */
class Customization
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
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
        if ($this->_assignedTheme === null) {
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
        if ($this->_unassignedTheme === null) {
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
        $storesByThemes = [];
        $stores = $this->_storeManager->getStores();
        /** @var $store \Magento\Store\Model\Store */
        foreach ($stores as $store) {
            $themeId = $this->_getConfigurationThemeId($store);
            if (!isset($storesByThemes[$themeId])) {
                $storesByThemes[$themeId] = [];
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
            ['store' => $store]
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
        /** @var \Magento\Theme\Model\ResourceModel\Theme\Collection $themeCollection */
        $themeCollection = $this->themeProvider->getThemeCustomizations(\Magento\Framework\App\Area::AREA_FRONTEND);

        $assignedThemes = $this->getStoresByThemes();

        $this->_assignedTheme = [];
        $this->_unassignedTheme = [];

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
