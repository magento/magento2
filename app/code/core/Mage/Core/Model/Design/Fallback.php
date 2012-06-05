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
class Mage_Core_Model_Design_Fallback
{
    /**
     * Design model
     *
     * @var Mage_Core_Model_Design_Package
     */
    protected $_design;

    /**
     * Constructor with $params, that must contain instance of Mage_Core_Model_Design_Package at 'design' key
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->_design = $params['design'];
    }

    /**
     * Use this one to get existing file name with fallback to default
     *
     * $params['_type'] is required
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getFilename($file, array $params = array())
    {
        Magento_Profiler::start(__METHOD__);
        try {
            $dir = Mage::getBaseDir('design');
            $dirs = array();
            $area = $params['_area'];
            $package = $params['_package'];
            $theme = $params['_theme'];
            $module = $params['_module'];

            while ($theme) {
                $dirs[] = "{$dir}/{$area}/{$package}/{$theme}";
                list($package, $theme) = $this->getInheritedTheme($area, $package, $theme);
            }

            $moduleDir = $module ? array(Mage::getConfig()->getModuleDir('view', $module) . "/{$area}") : array();
            Magento_Profiler::stop(__METHOD__);
        } catch (Exception $e) {
            Magento_Profiler::stop(__METHOD__);
            throw $e;
        }
        return $this->_fallback($file, $dirs, $module, $moduleDir);
    }

    /**
     * Path getter for locale file
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getLocaleFileName($file, array $params=array())
    {
        $area = $params['_area'];
        $package = $params['_package'];
        $theme = $params['_theme'];
        $locale = $params['_locale'];
        $dir = Mage::getBaseDir('design');

        $dirs = array();
        do {
            $dirs[] = "{$dir}/{$area}/{$package}/{$theme}/locale/{$locale}";
            list($package, $theme) = $this->getInheritedTheme($area, $package, $theme);
        } while ($theme);

        return $this->_fallback($file, $dirs);
    }

    /**
     * Find a skin file using fallback mechanism
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getSkinFile($file, $params)
    {
        $area = $params['_area'];
        $package = $params['_package'];
        $theme = $params['_theme'];
        $skin = $params['_skin'];
        $module = $params['_module'];
        $locale = $params['_locale'];
        $dir = Mage::getBaseDir('design');
        $moduleDir = $module ? Mage::getConfig()->getModuleDir('view', $module) : '';
        $defaultSkin = Mage_Core_Model_Design_Package::DEFAULT_SKIN_NAME;

        $dirs = array();
        while ($theme) {
            $dirs[] = "{$dir}/{$area}/{$package}/{$theme}/skin/{$skin}/locale/{$locale}";
            $dirs[] = "{$dir}/{$area}/{$package}/{$theme}/skin/{$skin}";
            if ($skin != $defaultSkin) {
                $dirs[] = "{$dir}/{$area}/{$package}/{$theme}/skin/{$defaultSkin}/locale/{$locale}";
                $dirs[] = "{$dir}/{$area}/{$package}/{$theme}/skin/{$defaultSkin}";
            }
            list($package, $theme) = $this->getInheritedTheme($area, $package, $theme);
        }

        return $this->_fallback(
            $file,
            $dirs,
            $module,
            array("{$moduleDir}/{$area}/locale/{$locale}", "{$moduleDir}/{$area}"),
            array(Mage::getBaseDir('js'))
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
        Magento_Profiler::start(__METHOD__);
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

        Magento_Profiler::stop(__METHOD__);
        return $tryFile;
    }

    /**
     * Get the name of the inherited theme
     *
     * If the specified theme inherits other theme the result is the name of inherited theme.
     * If the specified theme does not inherit other theme the result is false.
     *
     * @param string $area
     * @param string $package
     * @param string $theme
     * @return array|false
     */
    public function getInheritedTheme($area, $package, $theme)
    {
        $parentTheme = $this->_design->getThemeConfig($area)->getParentTheme($package, $theme);
        if (!$parentTheme) {
            return false;
        }
        $result = explode('/', $parentTheme, 2);
        if (count($result) > 1) {
            return $result;
        }
        return array($package, $parentTheme);
    }
}
