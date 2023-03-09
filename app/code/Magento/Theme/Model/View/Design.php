<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model\View;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\ThemeFactory;

/**
 * Keeps design settings for current request
 */
class Design implements DesignInterface
{
    /**
     * Package area
     *
     * @var string
     */
    protected $_area;

    /**
     * Package theme
     *
     * @var Theme
     */
    protected $_theme;

    /**
     * Directory of the css file
     * Using only to transmit additional parameter in callback functions
     *
     * @var string
     */
    protected $_callbackFileDir;

    /**
     * Store list manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var FlyweightFactory
     */
    protected $_flyweightFactory;

    /**
     * @var ThemeFactory
     */
    protected $_themeFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var ResolverInterface
     */
    protected $_locale;

    /**
     * @var State
     */
    protected $_appState;

    /**
     * @var array
     */
    private $_themes;

    /**
     * @param StoreManagerInterface $storeManager
     * @param FlyweightFactory $flyweightFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ThemeFactory $themeFactory
     * @param ObjectManagerInterface $objectManager
     * @param State $appState
     * @param array $themes
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        FlyweightFactory $flyweightFactory,
        ScopeConfigInterface $scopeConfig,
        ThemeFactory $themeFactory,
        protected readonly ObjectManagerInterface $objectManager,
        State $appState,
        array $themes
    ) {
        $this->_storeManager = $storeManager;
        $this->_flyweightFactory = $flyweightFactory;
        $this->_themeFactory = $themeFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_appState = $appState;
        $this->_themes = $themes;
    }

    /**
     * Set package area
     *
     * @param string $area
     * @return $this
     */
    public function setArea($area)
    {
        $this->_area = $area;
        $this->_theme = null;
        return $this;
    }

    /**
     * Retrieve package area
     *
     * @return string
     */
    public function getArea()
    {
        // In order to support environment emulation of area, if area is set, return it
        if ($this->_area && !$this->_appState->isAreaCodeEmulated()) {
            return $this->_area;
        }
        return $this->_appState->getAreaCode();
    }

    /**
     * Set theme path
     *
     * @param ThemeInterface|string $theme
     * @param string $area
     * @return $this
     */
    public function setDesignTheme($theme, $area = null)
    {
        if ($area) {
            $this->setArea($area);
        } else {
            $area = $this->getArea();
        }

        if ($theme instanceof ThemeInterface) {
            $this->_theme = $theme;
        } else {
            $this->_theme = $this->_flyweightFactory->create($theme, $area);
        }

        return $this;
    }

    /**
     * Get default theme which declared in configuration
     *
     * Write default theme to core_config_data
     *
     * @param string|null $area
     * @param array $params
     * @return string|int
     */
    public function getConfigurationDesignTheme($area = null, array $params = [])
    {
        if (!$area) {
            $area = $this->getArea();
        }

        $theme = null;
        $store = isset($params['store']) ? $params['store'] : null;

        if ($this->_isThemePerStoreView($area)) {
            if ($this->_storeManager->isSingleStoreMode()) {
                $theme = $this->_scopeConfig->getValue(
                    self::XML_PATH_THEME_ID,
                    ScopeInterface::SCOPE_WEBSITES
                );
            } else {
                $theme = (string) $this->_scopeConfig->getValue(
                    self::XML_PATH_THEME_ID,
                    ScopeInterface::SCOPE_STORE,
                    $store
                );
            }
        }

        if (!$theme && isset($this->_themes[$area])) {
            $theme = $this->_themes[$area];
        }

        return $theme;
    }

    /**
     * Whether themes in specified area are supposed to be configured per store view
     *
     * @param string $area
     * @return bool
     */
    private function _isThemePerStoreView($area)
    {
        return $area == self::DEFAULT_AREA;
    }

    /**
     * Set default design theme
     *
     * @return $this
     */
    public function setDefaultDesignTheme()
    {
        $this->setDesignTheme($this->getConfigurationDesignTheme());
        return $this;
    }

    /**
     * Design theme model getter
     *
     * @return Theme
     */
    public function getDesignTheme()
    {
        if ($this->_theme === null) {
            $this->_theme = $this->_themeFactory->create();
        }
        return $this->_theme;
    }

    /**
     * {@inheritdoc}
     */
    public function getThemePath(ThemeInterface $theme)
    {
        $themePath = $theme->getThemePath();
        if (!$themePath) {
            $themeId = $theme->getId();
            if ($themeId) {
                $themePath = self::PUBLIC_THEME_DIR . $themeId;
            } else {
                $themePath = self::PUBLIC_VIEW_DIR;
            }
        }
        return $themePath;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        if (null === $this->_locale) {
            $this->_locale = $this->objectManager->get(ResolverInterface::class);
        }
        return $this->_locale->getLocale();
    }

    /**
     * @param ResolverInterface $locale
     * @return $this
     */
    public function setLocale(ResolverInterface $locale)
    {
        $this->_locale = $locale;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDesignParams()
    {
        $params = [
            'area' => $this->getArea(),
            'themeModel' => $this->getDesignTheme(),
            'locale'     => $this->getLocale(),
        ];

        return $params;
    }
}
