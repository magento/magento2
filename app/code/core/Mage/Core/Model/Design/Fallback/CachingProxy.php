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
 * A proxy for Fallback model. This proxy processes fallback resolution calls by either using map of cached paths, or
 * passing resolution to the Fallback model.
 */
class Mage_Core_Model_Design_Fallback_CachingProxy implements Mage_Core_Model_Design_FallbackInterface
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
     * Whether object can save map changes upon destruction
     *
     * @var bool
     */
    protected $_canSaveMap;

    /**
     * Whether there were changes in map
     *
     * @var bool
     */
    protected $_isMapChanged = false;

    /**
     * Map full filename
     *
     * @var string
     */
    protected $_mapFile;

    /**
     * Cached fallback map
     *
     * @var array
     */
    protected $_map;

    /**
     * Proxied fallback model
     *
     * @var Mage_Core_Model_Design_Fallback
     */
    protected $_fallback;

    /**
     * Directory to keep map file
     *
     * @var string
     */
    protected $_mapDir;

    /**
     * Path to Magento base directory
     *
     * @var string
     */
    protected $_basePath;

    /**
     * Constructor.
     * Following entries in $params are required: 'area', 'package', 'theme', 'skin', 'locale', 'canSaveMap',
     * 'mapDir', 'baseDir'.
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
        $this->_canSaveMap = $params['canSaveMap'];
        $this->_mapDir = $params['mapDir'];
        $this->_basePath = $params['baseDir'] ? $params['baseDir'] . DIRECTORY_SEPARATOR : '';

        $this->_mapFile =
            "{$this->_mapDir}/{$this->_area}_{$this->_package}_{$this->_theme}_{$this->_skin}_{$this->_locale}.ser";
        $this->_map = file_exists($this->_mapFile) ? unserialize(file_get_contents($this->_mapFile)) : array();
    }

    public function __destruct()
    {
        if ($this->_isMapChanged && $this->_canSaveMap) {
            if (!is_dir($this->_mapDir)) {
                mkdir($this->_mapDir, 0777, true);
            }
            file_put_contents($this->_mapFile, serialize($this->_map), LOCK_EX);
        }
    }

    /**
     * Return instance of fallback model. Create it, if it has not been created yet.
     *
     * @return Mage_Core_Model_Design_Fallback
     */
    protected function _getFallback()
    {
        if (!$this->_fallback) {
            $this->_fallback = Mage::getModel('Mage_Core_Model_Design_Fallback', array(
                'area' => $this->_area,
                'package' => $this->_package,
                'theme' => $this->_theme,
                'skin' => $this->_skin,
                'locale' => $this->_locale
            ));
        }
        return $this->_fallback;
    }

    /**
     * Return relative file name from map
     *
     * @param string $prefix
     * @param string $file
     * @param string|null $module
     * @return string|null
     */
    protected function _getFromMap($prefix, $file, $module = null)
    {
        $mapKey = "$prefix|$file|$module";
        if (isset($this->_map[$mapKey])) {
            $value =  $this->_map[$mapKey];
            if ((string) $value !== '') {
                return $this->_basePath . $value;
            } else {
                return $value;
            }
        } else {
            return null;
        }
    }

    /**
     * Sets file to map. The file path must be within baseDir path.
     *
     * @param string $prefix
     * @param string $file
     * @param string|null $module
     * @param string $filePath
     * @return Mage_Core_Model_Design_Fallback_CachingProxy
     * @throws Mage_Core_Exception
     */
    protected function _setToMap($prefix, $file, $module, $filePath)
    {
        $basePathLen = strlen($this->_basePath);
        if (((string)$filePath !== '') && strncmp($filePath, $this->_basePath, $basePathLen)) {
            throw new Mage_Core_Exception(
                "Attempt to store fallback path '{$filePath}', which is not within '{$this->_basePath}'"
            );
        }

        $mapKey = "$prefix|$file|$module";
        $this->_map[$mapKey] = substr($filePath, $basePathLen);
        $this->_isMapChanged = true;
        return $this;
    }

    /**
     * Get existing file name, using map and fallback mechanism
     *
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getFile($file, $module = null)
    {
        $result = $this->_getFromMap('theme', $file, $module);
        if (!$result) {
            $result = $this->_getFallback()->getFile($file, $module);
            $this->_setToMap('theme', $file, $module, $result);
        }
        return $result;
    }

    /**
     * Get locale file name, using map and fallback mechanism
     *
     * @param string $file
     * @return string
     */
    public function getLocaleFile($file)
    {
        $result = $this->_getFromMap('locale', $file);
        if (!$result) {
            $result = $this->_getFallback()->getLocaleFile($file);
            $this->_setToMap('locale', $file, null, $result);
        }
        return $result;
    }

    /**
     * Get skin file name, using map and fallback mechanism
     *
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getSkinFile($file, $module = null)
    {
        $result = $this->_getFromMap('skin', $file, $module);
        if (!$result) {
            $result = $this->_getFallback()->getSkinFile($file, $module);
            $this->_setToMap('skin', $file, $module, $result);
        }
        return $result;
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
        return $this->_setToMap('skin', $file, $module, $publicFilePath);
    }
}
