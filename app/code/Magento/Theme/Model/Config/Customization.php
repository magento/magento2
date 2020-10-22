<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Config;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Theme\Model\Theme\StoreThemesResolverInterface;
use Magento\Theme\Model\Theme\StoreUserAgentThemeResolver;

/**
 * Theme customization config model
 */
class Customization
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var DesignInterface
     */
    protected $_design;

    /**
     * @var ThemeProviderInterface
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
     * @var StoreUserAgentThemeResolver|mixed|null
     */
    private $storeThemesResolver;

    /**
     * @param StoreManagerInterface $storeManager
     * @param DesignInterface $design
     * @param ThemeProviderInterface $themeProvider
     * @param StoreThemesResolverInterface|null $storeThemesResolver
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        DesignInterface $design,
        ThemeProviderInterface $themeProvider,
        ?StoreThemesResolverInterface $storeThemesResolver = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_design = $design;
        $this->themeProvider = $themeProvider;
        $this->storeThemesResolver = $storeThemesResolver
            ?? ObjectManager::getInstance()->get(StoreThemesResolverInterface::class);
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
        /** @var $store Store */
        foreach ($stores as $store) {
            foreach ($this->storeThemesResolver->getThemes($store) as $themeId) {
                if (!isset($storesByThemes[$themeId])) {
                    $storesByThemes[$themeId] = [];
                }
                $storesByThemes[$themeId][] = $store;
            }
        }
        return $storesByThemes;
    }

    /**
     * Check if current theme has assigned to any store
     *
     * @param ThemeInterface $theme
     * @param null|Store $store
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
     * @param ThemeInterface $theme
     * @param Store $store
     * @return bool
     */
    protected function _isThemeAssignedToSpecificStore($theme, $store)
    {
        return $theme->getId() == $this->_getConfigurationThemeId($store);
    }

    /**
     * Get configuration theme id
     *
     * @param Store $store
     * @return int
     */
    protected function _getConfigurationThemeId($store)
    {
        return $this->_design->getConfigurationDesignTheme(
            Area::AREA_FRONTEND,
            ['store' => $store]
        );
    }

    /**
     * Fetch theme customization and sort them out to arrays:
     *
     * Set self::_assignedTheme and self::_unassignedTheme.
     * NOTE: To get into "assigned" list theme customization not necessary should be assigned to store-view directly.
     * It can be set to website or as default theme and be used by store-view via config fallback mechanism.
     *
     * @return $this
     */
    protected function _prepareThemeCustomizations()
    {
        /** @var Collection $themeCollection */
        $themeCollection = $this->themeProvider->getThemeCustomizations(Area::AREA_FRONTEND);

        $assignedThemes = $this->getStoresByThemes();

        $this->_assignedTheme = [];
        $this->_unassignedTheme = [];

        /** @var $theme ThemeInterface */
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
