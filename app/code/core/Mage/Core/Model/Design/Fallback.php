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
 * Class for managing fallback of files
 */
class Mage_Core_Model_Design_Fallback implements Mage_Core_Model_Design_FallbackInterface
{
    /**
     * @var string
     */
    protected $_area;

    /**
     * @var Mage_Core_Model_Theme
     */
    protected $_theme;

    /**
     * @var string|null
     */
    protected $_locale;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_appConfig;

    /**
     * @var Mage_Core_Model_Dir
     */
    protected $_dirs = null;

    /**
     * @var Magento_ObjectManager|null
     */
    protected $_objectManager = null;

    /**
     * Constructor.
     * Following entries in $params are required: 'area', 'package', 'theme', 'locale'. The 'appConfig' and
     * 'themeConfig' may contain application config and theme config, respectively. If these these entries are not
     * present or null, then they will be retrieved from global application instance.
     *
     * @param Mage_Core_Model_Dir $dirs
     * @param Magento_ObjectManager $objectManager
     * @param Magento_Filesystem $filesystem
     * @param array $params
     * @throws InvalidArgumentException
     */
    public function __construct(
        Mage_Core_Model_Dir $dirs,
        Magento_ObjectManager $objectManager,
        Magento_Filesystem $filesystem,
        $params
    ) {
        $this->_dirs = $dirs;
        $this->_objectManager = $objectManager;
        $this->_filesystem = $filesystem;
        if (!array_key_exists('area', $params) || !array_key_exists('themeModel', $params)
            || !array_key_exists('locale', $params)
        ) {
            throw new InvalidArgumentException("Missing one of the param keys: 'area', 'themeModel', 'locale'.");
        }
        $this->_area = $params['area'];
        $this->_theme = $params['themeModel'];
        $this->_locale = $params['locale'];
    }

    /**
     * Get area code
     *
     * @return string
     */
    public function getArea()
    {
        return $this->_area;
    }

    /**
     * Get theme identification code
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->_theme->getId() ?: $this->_theme->getThemePath();
    }

    /**
     * Get locale code
     *
     * @return null|string
     */
    public function getLocale()
    {
        return $this->_locale;
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
        $dir = $this->_dirs->getDir(Mage_Core_Model_Dir::THEMES);
        $dirs = array();
        $themeModel = $this->_theme;
        while ($themeModel) {
            $themePath = $themeModel->getThemePath();
            if ($themePath) {
                $dirs[] = "{$dir}/{$this->_area}/{$themePath}";
            }
            $themeModel = $themeModel->getParentTheme();
        }

        if ($module) {
            $moduleDir = array($this->_objectManager->get('Mage_Core_Model_Config')->getModuleDir('view', $module)
                . "/{$this->_area}");
        } else {
            $moduleDir = array();
        }
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
        $dir = $this->_dirs->getDir(Mage_Core_Model_Dir::THEMES);
        $dirs = array();
        $themeModel = $this->_theme;
        while ($themeModel) {
            $themePath = $themeModel->getThemePath();
            if ($themePath) {
                $dirs[] = "{$dir}/{$this->_area}/{$themePath}/locale/{$this->_locale}";
            }
            $themeModel = $themeModel->getParentTheme();
        }

        return $this->_fallback($file, $dirs);
    }

    /**
     * Get theme file name, using fallback mechanism
     *
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getViewFile($file, $module = null)
    {
        $dir = $this->_dirs->getDir(Mage_Core_Model_Dir::THEMES);
        $moduleDir = $module ? $this->_objectManager->get('Mage_Core_Model_Config')->getModuleDir('view', $module) : '';

        $dirs = array();
        $themeModel = $this->_theme;
        while ($themeModel) {
            $themePath = $themeModel->getThemePath();
            if ($themePath) {
                $dirs[] = "{$dir}/{$this->_area}/{$themePath}/locale/{$this->_locale}";
                $dirs[] = "{$dir}/{$this->_area}/{$themePath}";
            }
            $themeModel = $themeModel->getParentTheme();
        }

        return $this->_fallback(
            $file,
            $dirs,
            $module,
            array("{$moduleDir}/{$this->_area}/locale/{$this->_locale}", "{$moduleDir}/{$this->_area}"),
            array($this->_dirs->getDir(Mage_Core_Model_Dir::PUB_LIB))
        );
    }

    /**
     * Go through specified directories and try to locate the file
     *
     * Returns the first found file or last file from the list as absolute path
     *
     * @param string $file relative file name
     * @param array $themeDirs theme directories (absolute paths) - must not be empty
     * @param string|bool $module module context
     * @param array $moduleDirs module directories (absolute paths, makes sense with previous parameter only)
     * @param array $extraDirs additional lookup directories (absolute paths)
     * @return string
     */
    protected function _fallback($file, $themeDirs, $module = false, $moduleDirs = array(), $extraDirs = array())
    {
        // add customization path
        $dirs = array();
        if ($this->_theme->getCustomizationPath()) {
            $dirs[] = $this->_theme->getCustomizationPath();
        }

        // add modules to lookup
        $dirs = array_merge($dirs, $themeDirs);
        if ($module) {
            array_walk($themeDirs, function (&$dir) use ($module) {
                $dir = "{$dir}/{$module}";
            });
            $dirs = array_merge($dirs, $themeDirs, $moduleDirs);
        }
        $dirs = array_merge($dirs, $extraDirs);

        // look for files
        $tryFile = '';
        foreach ($dirs as $dir) {
            $tryFile = str_replace('/', DIRECTORY_SEPARATOR, "{$dir}/{$file}");
            if ($this->_filesystem->has($tryFile)) {
                break;
            }
        }
        return $tryFile;
    }
}
