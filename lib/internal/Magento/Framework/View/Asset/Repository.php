<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

/**
 * A repository service for view assets
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
 */
class Repository implements ResetAfterRequestInterface
{
    /**
     * Scope separator for module notation of file ID
     */
    public const FILE_ID_SEPARATOR = '::';

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
     * @deprecated 100.0.2
     */
    private $themeList;

    /**
     * @var \Magento\Framework\View\Asset\Source
     */
    private $assetSource;

    /**
     * @var \Magento\Framework\View\Asset\ContextInterface[]|null
     */
    private $fallbackContext;

    /**
     * @var \Magento\Framework\View\Asset\ContextInterface[]|null
     */
    private $fileContext;

    /**
     * @var null|array
     */
    private $defaults = null;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var File\FallbackContextFactory
     */
    private $fallbackContextFactory;

    /**
     * @var File\ContextFactory
     */
    private $contextFactory;

    /**
     * @var RemoteFactory
     */
    private $remoteFactory;

    /**
     * @var ThemeProviderInterface|null
     */
    private $themeProvider;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @param \Magento\Framework\UrlInterface $baseUrl
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\View\Design\Theme\ListInterface $themeList
     * @param \Magento\Framework\View\Asset\Source $assetSource
     * @param \Magento\Framework\App\Request\Http $request
     * @param FileFactory $fileFactory
     * @param File\FallbackContextFactory $fallbackContextFactory
     * @param File\ContextFactory $contextFactory
     * @param RemoteFactory $remoteFactory
     */
    public function __construct(
        \Magento\Framework\UrlInterface $baseUrl,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\View\Design\Theme\ListInterface $themeList,
        \Magento\Framework\View\Asset\Source $assetSource,
        \Magento\Framework\App\Request\Http $request,
        FileFactory $fileFactory,
        File\FallbackContextFactory $fallbackContextFactory,
        File\ContextFactory $contextFactory,
        RemoteFactory $remoteFactory
    ) {
        $this->baseUrl = $baseUrl;
        $this->design = $design;
        $this->themeList = $themeList;
        $this->assetSource = $assetSource;
        $this->request = $request;
        $this->fileFactory = $fileFactory;
        $this->fallbackContextFactory = $fallbackContextFactory;
        $this->contextFactory = $contextFactory;
        $this->remoteFactory = $remoteFactory;
    }

    /**
     * Update required parameters with default values if custom not specified
     *
     * @param array &$params
     * @throws \UnexpectedValueException
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
            if (is_numeric($theme)) {
                $params['themeModel'] = $this->getThemeProvider()->getThemeById($theme);
            } else {
                $params['themeModel'] = $this->getThemeProvider()->getThemeByFullPath($area . '/' . $theme);
            }

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
     * Get theme provider
     *
     * @return ThemeProviderInterface
     */
    private function getThemeProvider()
    {
        if (null === $this->themeProvider) {
            $this->themeProvider = ObjectManager::getInstance()->get(ThemeProviderInterface::class);
        }

        return $this->themeProvider;
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
     * @throws LocalizedException
     */
    public function createAsset($fileId, array $params = [])
    {
        $this->updateDesignParams($params);
        list($module, $filePath) = self::extractModule($fileId);
        if (!$module && $params['module']) {
            $module = $params['module'];
        }

        if (!isset($params['publish'])) {
            $map = $this->getRepositoryFilesMap($fileId, $params);
            if ($map) {
                $params = array_replace($params, $map);
            }
        }

        $isSecure = isset($params['_secure']) ? (bool) $params['_secure'] : null;
        $themePath = isset($params['theme']) ? $params['theme'] : $this->design->getThemePath($params['themeModel']);
        $context = $this->getFallbackContext(
            UrlInterface::URL_TYPE_STATIC,
            $isSecure,
            $params['area'],
            $themePath,
            $params['locale']
        );
        return $this->fileFactory->create(
            [
                'source' => $this->assetSource,
                'context' => $context,
                'filePath' => $filePath,
                'module' => $module,
                'contentType' => $this->assetSource->getContentType($filePath)
            ]
        );
    }

    /**
     * Get current context for static view files
     *
     * @return \Magento\Framework\View\Asset\File\FallbackContext
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
            $this->fallbackContext[$id] = $this->fallbackContextFactory->create(
                [
                    'baseUrl' => $url,
                    'areaType' => $area,
                    'themePath' => $themePath,
                    'localeCode' => $locale
                ]
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
        return $this->fileFactory->create(
            [
                'source' => $this->assetSource,
                'context' => $similarTo->getContext(),
                'filePath' => $filePath,
                'module' => $module,
                'contentType' => $this->assetSource->getContentType($filePath)
            ]
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
        return $this->fileFactory->create(
            [
                'source' => $this->assetSource,
                'context' => $context,
                'filePath' => $filePath,
                'module' => '',
                'contentType' => $contentType
            ]
        );
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
            $this->fileContext[$id] = $this->contextFactory->create(
                ['baseUrl' => $url, 'baseDirType' => $baseDirType, 'contextPath' => $dirPath]
            );
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
     * @codeCoverageIgnore
     */
    public function createRemoteAsset($url, $contentType)
    {
        return $this->remoteFactory->create(['url' => $url, 'contentType' => $contentType]);
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public static function extractModule($fileId)
    {
        if (!$fileId || strpos($fileId, self::FILE_ID_SEPARATOR) === false) {
            return ['', $fileId];
        }
        $result = explode(self::FILE_ID_SEPARATOR, $fileId, 2);
        if (empty($result[0])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Scope separator "::" cannot be used without scope identifier.')
            );
        }
        return [$result[0], $result[1]];
    }

    /**
     * Get repository files map
     *
     * @param string $fileId
     * @param array $params
     * @return RepositoryMap
     */
    private function getRepositoryFilesMap($fileId, array $params)
    {
        $repositoryMap = ObjectManager::getInstance()->get(RepositoryMap::class);
        return $repositoryMap->getMap($fileId, $params);
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->fallbackContext = null;
        $this->fileContext = null;
        $this->defaults = null;
        $this->themeProvider = null;
    }
}
