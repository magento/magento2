<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * A repository service for view assets
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Repository
{
    /**
     * Scope separator for module notation of file ID
     */
    const FILE_ID_SEPARATOR = '::';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $baseUrl;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    private $design;

    /**
     * @var \Magento\Framework\View\Design\Theme\ListInterface
     */
    private $themeList;

    /**
     * @var \Magento\Framework\View\Asset\Source
     */
    private $assetSource;

    /**
     * @var \Magento\Framework\View\Asset\ContextInterface[]
     */
    private $fallbackContext;

    /**
     * @var \Magento\Framework\View\Asset\ContextInterface[]
     */
    private $fileContext;

    /**
     * @var null|array
     */
    private $defaults = null;

    /**
     * @param \Magento\Framework\UrlInterface $baseUrl
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\View\Design\Theme\ListInterface $themeList
     * @param \Magento\Framework\View\Asset\Source $assetSource
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\UrlInterface $baseUrl,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\View\Design\Theme\ListInterface $themeList,
        \Magento\Framework\View\Asset\Source $assetSource,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->baseUrl = $baseUrl;
        $this->design = $design;
        $this->themeList = $themeList;
        $this->assetSource = $assetSource;
        $this->request = $request;
    }

    /**
     * Update required parameters with default values if custom not specified
     *
     * @param array &$params
     * @throws \UnexpectedValueException
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function updateDesignParams(array &$params)
    {
        // Set area
        if (empty($params['area'])) {
            $params['area'] = $this->getDefaultParameter('area');
        }

        // Set themeModel
        $theme = null;
        $area = $params['area'];
        if (!empty($params['themeId'])) {
            $theme = $params['themeId'];
        } elseif (isset($params['theme'])) {
            $theme = $params['theme'];
        } elseif (empty($params['themeModel']) && $area !== $this->getDefaultParameter('area')) {
            $theme = $this->design->getConfigurationDesignTheme($area);
        }

        if ($theme) {
            $params['themeModel'] = $this->themeList->getThemeByFullPath($area . '/' . $theme);
            if (!$params['themeModel']) {
                throw new \UnexpectedValueException("Could not find theme '$theme' for area '$area'");
            }
        } elseif (empty($params['themeModel'])) {
            $params['themeModel'] = $this->getDefaultParameter('themeModel');
        }

        // Set module
        if (!array_key_exists('module', $params)) {
            $params['module'] = false;
        }

        // Set locale
        if (empty($params['locale'])) {
            $params['locale'] = $this->getDefaultParameter('locale');
        }
        return $this;
    }

    /**
     * Get default design parameter
     *
     * @param string $name
     * @return mixed
     */
    private function getDefaultParameter($name)
    {
        $this->defaults = $this->design->getDesignParams();
        return $this->defaults[$name];
    }

    /**
     * Create a file asset that's subject of fallback system
     *
     * @param string $fileId
     * @param array $params
     * @return File
     */
    public function createAsset($fileId, array $params = [])
    {
        $this->updateDesignParams($params);
        list($module, $filePath) = self::extractModule($fileId);
        if (!$module && $params['module']) {
            $module = $params['module'];
        }
        $isSecure = isset($params['_secure']) ? (bool) $params['_secure'] : null;
        $themePath = $this->design->getThemePath($params['themeModel']);
        $context = $this->getFallbackContext(
            UrlInterface::URL_TYPE_STATIC,
            $isSecure,
            $params['area'],
            $themePath,
            $params['locale']
        );
        return new File(
            $this->assetSource,
            $context,
            $filePath,
            $module,
            $this->assetSource->getContentType($filePath)
        );
    }

    /**
     * Get current context for static view files
     *
     * @return \Magento\Framework\View\Asset\ContextInterface
     */
    public function getStaticViewFileContext()
    {
        $params = [];
        $this->updateDesignParams($params);
        $themePath = $this->design->getThemePath($params['themeModel']);
        $isSecure = $this->request->isSecure();
        return $this->getFallbackContext(
            UrlInterface::URL_TYPE_STATIC,
            $isSecure,
            $params['area'],
            $themePath,
            $params['locale']
        );
    }

    /**
     * Get a fallback context value object
     *
     * Create only one instance per combination of parameters
     *
     * @param string $urlType
     * @param bool|null $isSecure
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @return \Magento\Framework\View\Asset\File\FallbackContext
     */
    private function getFallbackContext($urlType, $isSecure, $area, $themePath, $locale)
    {
        $secureKey = null === $isSecure ? 'null' : (int)$isSecure;
        $baseDirType = DirectoryList::STATIC_VIEW;
        $id = implode('|', [$baseDirType, $urlType, $secureKey, $area, $themePath, $locale]);
        if (!isset($this->fallbackContext[$id])) {
            $url = $this->baseUrl->getBaseUrl(['_type' => $urlType, '_secure' => $isSecure]);
            $this->fallbackContext[$id] = new \Magento\Framework\View\Asset\File\FallbackContext(
                $url,
                $area,
                $themePath,
                $locale,
                $isSecure
            );
        }
        return $this->fallbackContext[$id];
    }

    /**
     * Create a file asset similar to an existing local asset by using its context
     *
     * @param string $fileId
     * @param LocalInterface $similarTo
     * @return File
     */
    public function createSimilar($fileId, LocalInterface $similarTo)
    {
        list($module, $filePath) = self::extractModule($fileId);
        if (!$module) {
            $module = $similarTo->getModule();
        }
        return new File(
            $this->assetSource,
            $similarTo->getContext(),
            $filePath,
            $module,
            $this->assetSource->getContentType($filePath)
        );
    }

    /**
     * Create a file asset with an arbitrary path
     *
     * This kind of file is not subject of fallback system
     * Client code is responsible for ensuring that the file is in specified directory
     *
     * @param string $filePath
     * @param string $dirPath
     * @param string $baseDirType
     * @param string $baseUrlType
     * @return File
     */
    public function createArbitrary(
        $filePath,
        $dirPath,
        $baseDirType = DirectoryList::STATIC_VIEW,
        $baseUrlType = UrlInterface::URL_TYPE_STATIC
    ) {
        $context = $this->getFileContext($baseDirType, $baseUrlType, $dirPath);
        $contentType = $this->assetSource->getContentType($filePath);
        return new File($this->assetSource, $context, $filePath, '', $contentType);
    }

    /**
     * Get a file context value object
     *
     * Same instance per set of parameters
     *
     * @param string $baseDirType
     * @param string $urlType
     * @param string $dirPath
     * @return \Magento\Framework\View\Asset\File\Context
     */
    private function getFileContext($baseDirType, $urlType, $dirPath)
    {
        $id = implode('|', [$baseDirType, $urlType, $dirPath]);
        if (!isset($this->fileContext[$id])) {
            $url = $this->baseUrl->getBaseUrl(['_type' => $urlType]);
            $this->fileContext[$id] = new \Magento\Framework\View\Asset\File\Context($url, $baseDirType, $dirPath);
        }
        return $this->fileContext[$id];
    }

    /**
     * Create a file asset with path relative to specified local asset
     *
     * @param string $fileId
     * @param LocalInterface $relativeTo
     * @return File
     */
    public function createRelated($fileId, LocalInterface $relativeTo)
    {
        list($module, $filePath) = self::extractModule($fileId);
        if ($module) {
            return $this->createSimilar($fileId, $relativeTo);
        }
        $filePath = \Magento\Framework\View\FileSystem::getRelatedPath($relativeTo->getFilePath(), $filePath);
        return $this->createSimilar($filePath, $relativeTo);
    }

    /**
     * Create a remote asset value object
     *
     * @param string $url
     * @param string $contentType
     * @return Remote
     */
    public function createRemoteAsset($url, $contentType)
    {
        return new Remote($url, $contentType);
    }

    /**
     * Getter for static view file URL
     *
     * @param string $fileId
     * @return string
     */
    public function getUrl($fileId)
    {
        $asset = $this->createAsset($fileId);
        return $asset->getUrl();
    }

    /**
     * A getter for static view file URL with special parameters
     *
     * To omit parameters and have them automatically determined from application state, use getUrl()
     *
     * @param string $fileId
     * @param array $params
     * @return string
     * @see getUrl()
     */
    public function getUrlWithParams($fileId, array $params)
    {
        $asset = $this->createAsset($fileId, $params);
        return $asset->getUrl();
    }

    /**
     * Extract module name from specified file ID
     *
     * @param string $fileId
     * @return array
     * @throws \Magento\Framework\Exception
     */
    public static function extractModule($fileId)
    {
        if (strpos($fileId, self::FILE_ID_SEPARATOR) === false) {
            return ['', $fileId];
        }
        $result = explode(self::FILE_ID_SEPARATOR, $fileId, 2);
        if (empty($result[0])) {
            throw new \Magento\Framework\Exception('Scope separator "::" cannot be used without scope identifier.');
        }
        return [$result[0], $result[1]];
    }
}
