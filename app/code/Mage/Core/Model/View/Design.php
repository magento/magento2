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
 * Keeps design settings for current request
 */
class Mage_Core_Model_View_Design implements Mage_Core_Model_View_DesignInterface
{
    /**#@+
     * Common node path to theme design configuration
     */
    const XML_PATH_THEME    = 'design/theme/full_name';
    const XML_PATH_THEME_ID = 'design/theme/theme_id';
    /**#@-*/

    /**
     * Regular expressions matches cache
     *
     * @var array
     */
    private static $_regexMatchCache      = array();

    /**
     * Custom theme type cache
     *
     * @var array
     */
    private static $_customThemeTypeCache = array();

    /**
     * Package area
     *
     * @var string
     */
    protected $_area;

    /**
     * Package theme
     *
     * @var Mage_Core_Model_Theme
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
     * @var Mage_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Mage_Core_Model_Theme_FlyweightFactory
     */
    protected $_themeFactory;

    /**
     * Design
     *
     * @param Mage_Core_Model_StoreManagerInterface $storeManager
     * @param Mage_Core_Model_Theme_FlyweightFactory $themeFactory
     */
    public function __construct(
        Mage_Core_Model_StoreManagerInterface $storeManager,
        Mage_Core_Model_Theme_FlyweightFactory $themeFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_themeFactory = $themeFactory;
    }

    /**
     * Set package area
     *
     * @param string $area
     * @return Mage_Core_Model_View_Design
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
        if (is_null($this->_area)) {
            $this->_area = self::DEFAULT_AREA;
        }
        return $this->_area;
    }

    /**
     * Set theme path
     *
     * @param Mage_Core_Model_Theme|int|string $theme
     * @param string $area
     * @return Mage_Core_Model_View_Design
     */
    public function setDesignTheme($theme, $area = null)
    {
        if ($area) {
            $this->setArea($area);
        }

        if ($theme instanceof Mage_Core_Model_Theme) {
            $this->_theme = $theme;
        } else {
            $this->_theme = $this->_themeFactory->create($theme, $this->getArea());
        }

        return $this;
    }

    /**
     * Get default theme which declared in configuration
     *
     * Write default theme to core_config_data
     *
     * @param string $area
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
            $theme = $this->_storeManager->isSingleStoreMode()
                ? (string)Mage::getConfig()->getNode('default/' . self::XML_PATH_THEME_ID)
                : (string)Mage::getStoreConfig(self::XML_PATH_THEME_ID, $store);
        }

        return $theme ?: (string)Mage::getConfig()->getNode($area . '/' . self::XML_PATH_THEME);
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
     * @return Mage_Core_Model_View_Design
     */
    public function setDefaultDesignTheme()
    {
        $this->setDesignTheme($this->getConfigurationDesignTheme());
        return $this;
    }

    /**
     * Design theme model getter
     *
     * @return Mage_Core_Model_Theme
     */
    public function getDesignTheme()
    {
        if ($this->_theme === null) {
            $this->_theme = Mage::getModel('Mage_Core_Model_Theme');
        }
        return $this->_theme;
    }

    /**
     * Return package name based on design exception rules
     *
     * @param array $rules - design exception rules
     * @param string $regexpsConfigPath
     * @return bool|string
     */
    public static function getPackageByUserAgent(array $rules, $regexpsConfigPath = 'path_mock')
    {
        foreach ($rules as $rule) {
            if (!empty(self::$_regexMatchCache[$rule['regexp']][$_SERVER['HTTP_USER_AGENT']])) {
                self::$_customThemeTypeCache[$regexpsConfigPath] = $rule['value'];
                return $rule['value'];
            }

            $regexp = '/' . trim($rule['regexp'], '/') . '/';

            if (@preg_match($regexp, $_SERVER['HTTP_USER_AGENT'])) {
                self::$_regexMatchCache[$rule['regexp']][$_SERVER['HTTP_USER_AGENT']] = true;
                self::$_customThemeTypeCache[$regexpsConfigPath] = $rule['value'];
                return $rule['value'];
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDesignParams()
    {
        $params = array(
            'area'       => $this->getArea(),
            'themeModel' => $this->getDesignTheme(),
            'locale'     => Mage::app()->getLocale()->getLocaleCode()
        );

        return $params;
    }
}
