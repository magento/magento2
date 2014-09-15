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

namespace Magento\Core\Model\View;

/**
 * Keeps design settings for current request
 */
class Design implements \Magento\Framework\View\DesignInterface
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
     * @var \Magento\Core\Model\Theme
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
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory
     */
    protected $_flyweightFactory;

    /**
     * @var \Magento\Core\Model\ThemeFactory
     */
    protected $_themeFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_locale;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Design\Theme\FlyweightFactory $flyweightFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Core\Model\ThemeFactory $themeFactory
     * @param \Magento\Framework\Locale\ResolverInterface $locale
     * @param \Magento\Framework\App\State $appState
     * @param array $themes
     */
    public function __construct(
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Design\Theme\FlyweightFactory $flyweightFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Core\Model\ThemeFactory $themeFactory,
        \Magento\Framework\Locale\ResolverInterface $locale,
        \Magento\Framework\App\State $appState,
        array $themes
    ) {
        $this->_storeManager = $storeManager;
        $this->_flyweightFactory = $flyweightFactory;
        $this->_themeFactory = $themeFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_appState = $appState;
        $this->_themes = $themes;
        $this->_locale = $locale;
    }

    /**
     * Set package area
     *
     * @param string $area
     * @return $this
     * @deprecated
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
        return $this->_appState->getAreaCode();
    }

    /**
     * Set theme path
     *
     * @param \Magento\Framework\View\Design\ThemeInterface|string $theme
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

        if ($theme instanceof \Magento\Framework\View\Design\ThemeInterface) {
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
    public function getConfigurationDesignTheme($area = null, array $params = array())
    {
        if (!$area) {
            $area = $this->getArea();
        }

        $theme = null;
        $store = isset($params['store']) ? $params['store'] : null;

        if ($this->_isThemePerStoveView($area)) {
            if ($this->_storeManager->isSingleStoreMode()) {
                $theme = $this->_scopeConfig->getValue(
                    self::XML_PATH_THEME_ID,
                    \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
                );
            } else {
                $theme = (string) $this->_scopeConfig->getValue(
                    self::XML_PATH_THEME_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
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
    private function _isThemePerStoveView($area)
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
     * @return \Magento\Core\Model\Theme
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
    public function getThemePath(\Magento\Framework\View\Design\ThemeInterface $theme)
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
        return $this->_locale->getLocaleCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getDesignParams()
    {
        $params = array(
            'area' => $this->getArea(),
            'themeModel' => $this->getDesignTheme(),
            'locale'     => $this->getLocale(),
        );

        return $params;
    }
}
