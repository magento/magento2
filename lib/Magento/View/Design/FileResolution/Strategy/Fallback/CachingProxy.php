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

namespace Magento\View\Design\FileResolution\Strategy\Fallback;

use Magento\App\Filesystem;
use Magento\View\Design\FileResolution\Strategy\Fallback;
use Magento\View\Design\FileResolution\Strategy\FileInterface;
use Magento\View\Design\FileResolution\Strategy\LocaleInterface;
use Magento\View\Design\FileResolution\Strategy\View\NotifiableInterface;
use Magento\View\Design\FileResolution\Strategy\ViewInterface;
use Magento\View\Design\ThemeInterface;
use Magento\Filesystem\Directory\Write;

/**
 * Caching Proxy
 *
 * A proxy for the Fallback resolver. This proxy processes fallback resolution calls by either using map of cached
 * paths, or passing resolution to the Fallback resolver.
 */
class CachingProxy implements FileInterface, LocaleInterface, ViewInterface, NotifiableInterface
{
    /**
     * Proxied fallback model
     *
     * @var Fallback
     */
    protected $fallback;

    /**
     * Path to maps directory
     *
     * @var string
     */
    protected $mapDir;

    /**
     * Path to Magento base directory
     *
     * @var string
     */
    protected $baseDir;

    /**
     * Whether object can save map changes upon destruction
     *
     * @var bool
     */
    protected $canSaveMap;

    /**
     * Var directory
     *
     * @var Write
     */
    protected $varDirectory;

    /**
     * Cached fallback map sections
     *
     * @var array
     */
    protected $sections = array();

    /**
     * Constructor
     *
     * @param Fallback $fallback
     * @param Filesystem $filesystem
     * @param string $mapDir
     * @param string $baseDir
     * @param bool $canSaveMap
     * @throws \InvalidArgumentException
     */
    public function __construct(
        Fallback $fallback,
        Filesystem $filesystem,
        $mapDir,
        $baseDir, // magento root
        $canSaveMap = true
    ) {
        $this->fallback = $fallback;
        $this->varDirectory = $filesystem->getDirectoryWrite(Filesystem::VAR_DIR);
        $rootDirectory = $filesystem->getDirectoryRead(Filesystem::ROOT_DIR);
        if (!$rootDirectory->isDirectory($rootDirectory->getRelativePath($baseDir))) {
            throw new \InvalidArgumentException("Wrong base directory specified: '{$baseDir}'");
        }
        $this->baseDir = $baseDir;
        $this->mapDir = $this->varDirectory->getRelativePath($mapDir);
        $this->canSaveMap = $canSaveMap;
    }

    /**
     * Write the serialized map to the section files
     */
    public function __destruct()
    {
        if (!$this->canSaveMap) {
            return;
        }
        if (!$this->varDirectory->isDirectory($this->mapDir)) {
            $this->varDirectory->create($this->mapDir);
        }
        foreach ($this->sections as $sectionFile => $section) {
            if (!$section['is_changed']) {
                continue;
            }
            $filePath = $this->mapDir . '/' . $sectionFile;
            $this->varDirectory->writeFile($filePath, serialize($section['data']));
        }
    }

    /**
     * Proxy to \Magento\View\Design\FileResolution\Strategy\Fallback::getFile()
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getFile($area, ThemeInterface $themeModel, $file, $module = null)
    {
        $result = $this->getFromMap('file', $area, $themeModel, null, $module, $file);
        if (!$result) {
            $result = $this->fallback->getFile($area, $themeModel, $file, $module);
            $this->setToMap('file', $area, $themeModel, null, $module, $file, $result);
        }
        return $result;
    }

    /**
     * Proxy to \Magento\View\Design\FileResolution\Strategy\Fallback::getLocaleFile()
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string $locale
     * @param string $file
     * @return string
     */
    public function getLocaleFile($area, ThemeInterface $themeModel, $locale, $file)
    {
        $result = $this->getFromMap('locale', $area, $themeModel, $locale, null, $file);
        if (!$result) {
            $result = $this->fallback->getLocaleFile($area, $themeModel, $locale, $file);
            $this->getFromMap('locale', $area, $themeModel, $locale, null, $file, $result);
        }
        return $result;
    }

    /**
     * Proxy to \Magento\View\Design\FileResolution\Strategy\Fallback::getViewFile()
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string $locale
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getViewFile($area, ThemeInterface $themeModel, $locale, $file, $module = null)
    {
        $result = $this->getFromMap('view', $area, $themeModel, $locale, $module, $file);
        if (!$result) {
            $result = $this->fallback->getViewFile($area, $themeModel, $locale, $file, $module);
            $this->getFromMap('view', $area, $themeModel, $locale, $module, $file, $result);
        }
        return $result;
    }

    /**
     * Get stored full file path
     *
     * @param string $fileType
     * @param string $area
     * @param ThemeInterface $theme
     * @param string|null $locale
     * @param string|null $module
     * @param string $file
     * @return null|string
     */
    protected function getFromMap($fileType, $area, ThemeInterface $theme, $locale, $module, $file)
    {
        $sectionKey = $this->loadSection($area, $theme, $locale);
        $fileKey = "$fileType|$file|$module";
        if (isset($this->sections[$sectionKey]['data'][$fileKey])) {
            $value = $this->sections[$sectionKey]['data'][$fileKey];
            if ('' !== (string)$value) {
                $value = $this->baseDir . '/' . $value;
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
     * @param ThemeInterface $theme
     * @param string|null $locale
     * @param string|null $module
     * @param string $file
     * @param string $filePath
     * @return void
     * @throws \Magento\Exception
     */
    protected function setToMap($fileType, $area, ThemeInterface $theme, $locale, $module, $file, $filePath)
    {
        if (0 !== strpos($filePath, $this->baseDir)) {
            throw new \Magento\Exception(
                "Attempt to store fallback path '{$filePath}', which is not within '{$this->baseDir}'"
            );
        }
        $value = ltrim(substr($filePath, strlen($this->baseDir)), '/\\');

        $sectionKey = $this->loadSection($area, $theme, $locale);
        $fileKey = "$fileType|$file|$module";
        $this->sections[$sectionKey]['data'][$fileKey] = $value;
        $this->sections[$sectionKey]['is_changed'] = true;
    }

    /**
     * Compose section file name
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string|null $locale
     * @return string
     */
    protected function getSectionFile($area, ThemeInterface $themeModel, $locale)
    {
        $theme = $themeModel->getId() ?: md5($themeModel->getThemePath());
        return "{$area}_{$theme}_{$locale}.ser";
    }

    /**
     * Load section and return its key
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string|null $locale
     * @return string
     */
    protected function loadSection($area, ThemeInterface $themeModel, $locale)
    {
        $sectionFile = $this->getSectionFile($area, $themeModel, $locale);
        if (!isset($this->sections[$sectionFile])) {
            $filePath = $this->mapDir . '/' . $sectionFile;
            $this->sections[$sectionFile] = array(
                'data' => array(),
                'is_changed' => false,
            );
            if ($this->varDirectory->isFile($filePath)) {
                $this->sections[$sectionFile]['data'] = unserialize($this->varDirectory->readFile($filePath));
            }
        }
        return $sectionFile;
    }

    /**
     * Set file path to map.
     *
     * @param string $area
     * @param ThemeInterface $themeModel
     * @param string $locale
     * @param string|null $module
     * @param string $file
     * @param string $newFilePath
     * @return $this
     */
    public function setViewFilePathToMap($area, ThemeInterface $themeModel, $locale, $module, $file, $newFilePath)
    {
        $this->setToMap('view', $area, $themeModel, $locale, $module, $file, $newFilePath);
        return $this;
    }
}
