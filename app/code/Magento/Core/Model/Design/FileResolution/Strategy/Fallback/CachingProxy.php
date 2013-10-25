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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * A proxy for the Fallback resolver. This proxy processes fallback resolution calls by either using map of cached
 * paths, or passing resolution to the Fallback resolver.
 */
namespace Magento\Core\Model\Design\FileResolution\Strategy\Fallback;

class CachingProxy implements
    \Magento\Core\Model\Design\FileResolution\Strategy\FileInterface,
    \Magento\Core\Model\Design\FileResolution\Strategy\LocaleInterface,
    \Magento\Core\Model\Design\FileResolution\Strategy\ViewInterface,
    \Magento\Core\Model\Design\FileResolution\Strategy\View\NotifiableInterface
{
    /**
     * Proxied fallback model
     *
     * @var \Magento\Core\Model\Design\FileResolution\Strategy\Fallback
     */
    protected $_fallback;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * Path to maps directory
     *
     * @var string
     */
    protected $_mapDir;

    /**
     * Path to Magento base directory
     *
     * @var string
     */
    protected $_baseDir;

    /**
     * Whether object can save map changes upon destruction
     *
     * @var bool
     */
    protected $_canSaveMap;

    /**
     * Cached fallback map sections
     *
     * @var array
     */
    protected $_sections = array();

    /**
     * @param \Magento\Core\Model\Design\FileResolution\Strategy\Fallback $fallback
     * @param \Magento\Filesystem $filesystem
     * @param string $mapDir
     * @param string $baseDir
     * @param bool $canSaveMap
     * @throws \InvalidArgumentException
     */
    public function __construct(
        \Magento\Core\Model\Design\FileResolution\Strategy\Fallback $fallback,
        \Magento\Filesystem $filesystem,
        $mapDir,
        $baseDir,
        $canSaveMap = true
    ) {
        $this->_fallback = $fallback;
        $this->_filesystem = $filesystem;
        if (!$filesystem->isDirectory($baseDir)) {
            throw new \InvalidArgumentException("Wrong base directory specified: '{$baseDir}'");
        }
        $this->_baseDir = $baseDir;
        $this->_mapDir = $mapDir;
        $this->_canSaveMap = $canSaveMap;
    }

    /**
     * Write the serialized map to the section files
     */
    public function __destruct()
    {
        if (!$this->_canSaveMap) {
            return;
        }
        if (!$this->_filesystem->isDirectory($this->_mapDir)) {
            $this->_filesystem->createDirectory($this->_mapDir, 0777);
        }
        foreach ($this->_sections as $sectionFile => $section) {
            if (!$section['is_changed']) {
                continue;
            }
            $filePath = $this->_mapDir . DIRECTORY_SEPARATOR . $sectionFile;
            $this->_filesystem->write($filePath, serialize($section['data']));
        }
    }

    /**
     * Proxy to \Magento\Core\Model\Design\FileResolution\Strategy\Fallback::getFile()
     *
     * @param string $area
     * @param \Magento\View\Design\ThemeInterface $themeModel
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getFile($area, \Magento\View\Design\ThemeInterface $themeModel, $file, $module = null)
    {
        $result = $this->_getFromMap('file', $area, $themeModel, null, $module, $file);
        if (!$result) {
            $result = $this->_fallback->getFile($area, $themeModel, $file, $module);
            $this->_setToMap('file', $area, $themeModel, null, $module, $file, $result);
        }
        return $result;
    }

    /**
     * Proxy to \Magento\Core\Model\Design\FileResolution\Strategy\Fallback::getLocaleFile()
     *
     * @param string $area
     * @param \Magento\View\Design\ThemeInterface $themeModel
     * @param string $locale
     * @param string $file
     * @return string
     */
    public function getLocaleFile($area, \Magento\View\Design\ThemeInterface $themeModel, $locale, $file)
    {
        $result = $this->_getFromMap('locale', $area, $themeModel, $locale, null, $file);
        if (!$result) {
            $result = $this->_fallback->getLocaleFile($area, $themeModel, $locale, $file);
            $this->_getFromMap('locale', $area, $themeModel, $locale, null, $file, $result);
        }
        return $result;
    }

    /**
     * Proxy to \Magento\Core\Model\Design\FileResolution\Strategy\Fallback::getViewFile()
     *
     * @param string $area
     * @param \Magento\View\Design\ThemeInterface $themeModel
     * @param string $locale
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getViewFile($area, \Magento\View\Design\ThemeInterface $themeModel, $locale, $file, $module = null)
    {
        $result = $this->_getFromMap('view', $area, $themeModel, $locale, $module, $file);
        if (!$result) {
            $result = $this->_fallback->getViewFile($area, $themeModel, $locale, $file, $module);
            $this->_getFromMap('view', $area, $themeModel, $locale, $module, $file, $result);
        }
        return $result;
    }

    /**
     * Get stored full file path
     *
     * @param string $fileType
     * @param string $area
     * @param \Magento\View\Design\ThemeInterface $theme
     * @param string|null $locale
     * @param string|null $module
     * @param string $file
     * @return null|string
     */
    protected function _getFromMap(
        $fileType, $area, \Magento\View\Design\ThemeInterface $theme, $locale, $module, $file
    ) {
        $sectionKey = $this->_loadSection($area, $theme, $locale);
        $fileKey = "$fileType|$file|$module";
        if (isset($this->_sections[$sectionKey]['data'][$fileKey])) {
            $value = $this->_sections[$sectionKey]['data'][$fileKey];
            if ('' !== (string)$value) {
                $value = $this->_baseDir . DIRECTORY_SEPARATOR . $value;
            }
            return $value;
        }
        return null;
    }

    /**
     * Set stored full file path
     *
     * @param string $fileType
     * @param string $area
     * @param \Magento\View\Design\ThemeInterface $theme
     * @param string|null $locale
     * @param string|null $module
     * @param string $file
     * @param string $filePath
     * @throws \Magento\Exception
     */
    protected function _setToMap(
        $fileType, $area, \Magento\View\Design\ThemeInterface $theme, $locale, $module, $file, $filePath
    ) {
        $pattern = $this->_baseDir . DIRECTORY_SEPARATOR;
        if (0 !== strpos($filePath, $pattern)) {
            throw new \Magento\Exception(
                "Attempt to store fallback path '{$filePath}', which is not within '{$pattern}'"
            );
        }
        $value = substr($filePath, strlen($pattern));

        $sectionKey = $this->_loadSection($area, $theme, $locale);
        $fileKey = "$fileType|$file|$module";
        $this->_sections[$sectionKey]['data'][$fileKey] = $value;
        $this->_sections[$sectionKey]['is_changed'] = true;
    }

    /**
     * Compose section file name
     *
     * @param string $area
     * @param \Magento\View\Design\ThemeInterface $themeModel
     * @param string|null $locale
     * @return string
     */
    protected function _getSectionFile($area, \Magento\View\Design\ThemeInterface $themeModel, $locale)
    {
        $theme = $themeModel->getId() ?: md5($themeModel->getThemePath());
        return "{$area}_{$theme}_{$locale}.ser";
    }

    /**
     * Load section and return its key
     *
     * @param string $area
     * @param \Magento\View\Design\ThemeInterface $themeModel
     * @param string|null $locale
     * @return string
     */
    protected function _loadSection($area, \Magento\View\Design\ThemeInterface $themeModel, $locale)
    {
        $sectionFile = $this->_getSectionFile($area, $themeModel, $locale);
        if (!isset($this->_sections[$sectionFile])) {
            $filePath = $this->_mapDir . DIRECTORY_SEPARATOR . $sectionFile;
            $this->_sections[$sectionFile] = array(
                'data' => array(),
                'is_changed' => false,
            );
            if ($this->_filesystem->isFile($filePath)) {
                $this->_sections[$sectionFile]['data'] = unserialize($this->_filesystem->read($filePath));
            }
        }
        return $sectionFile;
    }

    /**
     * Set file path to map.
     *
     * @param string $area
     * @param \Magento\View\Design\ThemeInterface $themeModel
     * @param string $locale
     * @param string|null $module
     * @param string $file
     * @param string $newFilePath
     * @return \Magento\Core\Model\Design\FileResolution\Strategy\Fallback\CachingProxy
     */
    public function setViewFilePathToMap(
        $area, \Magento\View\Design\ThemeInterface $themeModel, $locale, $module, $file, $newFilePath
    ) {
        $this->_setToMap('view', $area, $themeModel, $locale, $module, $file, $newFilePath);
        return $this;
    }
}
