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
 * Class for managing fallback of files
 */
class Mage_Core_Model_Design_Fallback implements Mage_Core_Model_Design_FallbackInterface
{
    /**
     * @var string
     */
    protected $_area;

    /**
     * @var string
     */
    protected $_package;

    /**
     * @var string
     */
    protected $_theme;

    /**
     * @var string|null
     */
    protected $_skin;

    /**
     * @var string|null
     */
    protected $_locale;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_appConfig;

    /**
     * @var Magento_Config_Theme
     */
    protected $_themeConfig;

    /**
     * Constructor.
     * Following entries in $params are required: 'area', 'package', 'theme', 'skin', 'locale'. The 'appConfig' and
     * 'themeConfig' may contain application config and theme config, respectively. If these these entries are not
     * present or null, then they will be retrieved from global application instance.
     *
     * @param array $params
     */
    public function __construct($params)
    {
        $this->_area = $params['area'];
        $this->_package = $params['package'];
        $this->_theme = $params['theme'];
        $this->_skin = $params['skin'];
        $this->_locale = $params['locale'];
        $this->_appConfig = isset($params['appConfig']) ? $params['appConfig'] : Mage::getConfig();
        $this->_themeConfig = isset($params['themeConfig']) ? $params['themeConfig']
            : Mage::getDesign()->getThemeConfig($this->_area);
    }

    /**
     * Get existing file name, using fallback mechanism
     *
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getFile($file, $module = null)
    {
        $dir = $this->_appConfig->getOptions()->getDesignDir();
        $dirs = array();
        $theme = $this->_theme;
        $package = $this->_package;
        while ($theme) {
            $dirs[] = "{$dir}/{$this->_area}/{$package}/{$theme}";
            list($package, $theme) = $this->_getInheritedTheme($package, $theme);
        }

        $moduleDir = $module ? array($this->_appConfig->getModuleDir('view', $module) . "/{$this->_area}") : array();
        return $this->_fallback($file, $dirs, $module, $moduleDir);
    }

    /**
     * Get locale file name, using fallback mechanism
     *
     * @param string $file
     * @return string
     */
    public function getLocaleFile($file)
    {
        $dir = $this->_appConfig->getOptions()->getDesignDir();
        $dirs = array();
        $package = $this->_package;
        $theme = $this->_theme;
        do {
            $dirs[] = "{$dir}/{$this->_area}/{$package}/{$theme}/locale/{$this->_locale}";
            list($package, $theme) = $this->_getInheritedTheme($package, $theme);
        } while ($theme);

        return $this->_fallback($file, $dirs);
    }

    /**
     * Get skin file name, using fallback mechanism
     *
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getSkinFile($file, $module = null)
    {
        $dir = $this->_appConfig->getOptions()->getDesignDir();
        $moduleDir = $module ? $this->_appConfig->getModuleDir('view', $module) : '';
        $defaultSkin = Mage_Core_Model_Design_Package::DEFAULT_SKIN_NAME;

        $dirs = array();
        $theme = $this->_theme;
        $package = $this->_package;
        while ($theme) {
            $dirs[] = "{$dir}/{$this->_area}/{$package}/{$theme}/skin/{$this->_skin}/locale/{$this->_locale}";
            $dirs[] = "{$dir}/{$this->_area}/{$package}/{$theme}/skin/{$this->_skin}";
            if ($this->_skin != $defaultSkin) {
                $dirs[] = "{$dir}/{$this->_area}/{$package}/{$theme}/skin/{$defaultSkin}/locale/{$this->_locale}";
                $dirs[] = "{$dir}/{$this->_area}/{$package}/{$theme}/skin/{$defaultSkin}";
            }
            list($package, $theme) = $this->_getInheritedTheme($package, $theme);
        }

        return $this->_fallback(
            $file,
            $dirs,
            $module,
            array("{$moduleDir}/{$this->_area}/locale/{$this->_locale}", "{$moduleDir}/{$this->_area}"),
            array($this->_appConfig->getOptions()->getJsDir())
        );
    }

    /**
     * Go through specified directories and try to locate the file
     *
     * Returns the first found file or last file from the list as absolute path
     *
     * @param string $file relative file name
     * @param array $themeDirs theme directories (absolute paths) - must not be empty
     * @param string|false $module module context
     * @param array $moduleDirs module directories (absolute paths, makes sense with previous parameter only)
     * @param array $extraDirs additional lookup directories (absolute paths)
     * @return string
     */
    protected function _fallback($file, $themeDirs, $module = false, $moduleDirs = array(), $extraDirs = array())
    {
        // add modules to lookup
        $dirs = $themeDirs;
        if ($module) {
            array_walk($themeDirs, function(&$dir) use ($module) {
                $dir = "{$dir}/{$module}";
            });
            $dirs = array_merge($themeDirs, $moduleDirs);
        }
        $dirs = array_merge($dirs, $extraDirs);
        // look for files
        $tryFile = '';
        foreach ($dirs as $dir) {
            $tryFile = str_replace('/', DIRECTORY_SEPARATOR, "{$dir}/{$file}");
            if (file_exists($tryFile)) {
                break;
            }
        }
        return $tryFile;
    }

    /**
     * Get the name of the inherited theme
     *
     * If the specified theme inherits other theme the result is the name of inherited theme.
     * If the specified theme does not inherit other theme the result is null.
     *
     * @param string $package
     * @param string $theme
     * @return string|null
     */
    protected function _getInheritedTheme($package, $theme)
    {
        return $this->_themeConfig->getParentTheme($package, $theme);
    }

    /**
     * Object notified, that skin file was published, thus it can return published file name on next calls
     *
     * @param string $publicFilePath
     * @param string $file
     * @param string|null $module
     * @return Mage_Core_Model_Design_FallbackInterface
     */
    public function notifySkinFilePublished($publicFilePath, $file, $module = null)
    {
        // Do nothing - we don't cache file paths in real fallback
        return $this;
    }
}
