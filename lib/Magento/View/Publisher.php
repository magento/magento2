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

namespace Magento\View;

use Magento\Filesystem\Directory\WriteInterface;

class Publisher implements \Magento\View\PublicFilesManagerInterface
{
    /**#@+
     * Extensions group for static files
     */
    const CONTENT_TYPE_CSS = 'css';
    const CONTENT_TYPE_JS  = 'js';
    /**#@-*/

    /**#@+
     * Protected extensions group for publication mechanism
     */
    const CONTENT_TYPE_PHP   = 'php';
    const CONTENT_TYPE_PHTML = 'phtml';
    const CONTENT_TYPE_XML   = 'xml';
    /**#@-*/

    /**#@+
     * Public directories prefix group
     */
    const PUBLIC_MODULE_DIR = '_module';
    const PUBLIC_VIEW_DIR   = '_view';
    const PUBLIC_THEME_DIR  = '_theme';
    /**#@-*/

    /**
     * File system
     *
     * @var \Magento\App\Filesystem
     */
    protected $_filesystem;

    /**
     * Helper to process css content
     *
     * @var \Magento\View\Url\CssResolver
     */
    protected $_cssUrlResolver;

    /**
     * View service
     *
     * @var \Magento\View\Service
     */
    protected $_viewService;

    /**
     * View file system
     *
     * @var \Magento\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * Logger
     *
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * Indicates how to materialize view files: with or without "duplication"
     *
     * @var bool
     */
    protected $_allowDuplication;

    /**
     * Modules reader
     *
     * @var \Magento\Module\Dir\Reader
     */
    protected $_modulesReader;

    /**
     * Root directory
     *
     * @var WriteInterface
     */
    protected $rootDirectory;

    /**
     * Related file
     *
     * @var RelatedFile
     */
    protected $relatedFile;

    /**
     * Pre-processor
     *
     * @var \Magento\View\Asset\PreProcessor\PreProcessorInterface
     */
    protected $preProcessor;

    /**
     * Constructor
     *
     * @param \Magento\Logger $logger
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\View\Url\CssResolver $cssUrlResolver
     * @param \Magento\View\Service $viewService
     * @param \Magento\View\FileSystem $viewFileSystem
     * @param \Magento\Module\Dir\Reader $modulesReader
     * @param RelatedFile $relatedFile
     * @param \Magento\View\Asset\PreProcessor\PreProcessorInterface $preProcessor
     * @param bool $allowDuplication
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\App\Filesystem $filesystem,
        \Magento\View\Url\CssResolver $cssUrlResolver,
        \Magento\View\Service $viewService,
        \Magento\View\FileSystem $viewFileSystem,
        \Magento\Module\Dir\Reader $modulesReader,
        RelatedFile $relatedFile,
        \Magento\View\Asset\PreProcessor\PreProcessorInterface $preProcessor,
        $allowDuplication
    ) {
        $this->_filesystem = $filesystem;
        $this->rootDirectory = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::ROOT_DIR);
        $this->_cssUrlResolver = $cssUrlResolver;
        $this->_viewService = $viewService;
        $this->_viewFileSystem = $viewFileSystem;
        $this->_modulesReader = $modulesReader;
        $this->_logger = $logger;
        $this->_allowDuplication = $allowDuplication;
        $this->relatedFile = $relatedFile;
        $this->preProcessor = $preProcessor;
    }

    /**
     * Get published file path
     *
     * @param  string $filePath
     * @param  array $params
     * @return string
     */
    public function getPublicFilePath($filePath, $params)
    {
        return $this->_getPublishedFilePath($filePath, $params);
    }

    /**
     * Publish file identified by $fileId basing on information about parent file path and name.
     *
     * @param string $fileId URL to the file that was extracted from $parentFilePath
     * @param string $parentFilePath path to the file
     * @param string $parentFileName original file name identifier that was requested for processing
     * @param array $params theme/module parameters array
     * @return string
     */
    protected function _publishRelatedViewFile($fileId, $parentFilePath, $parentFileName, $params)
    {
        $relativeFilePath = $this->relatedFile->buildPath($fileId, $parentFilePath, $parentFileName, $params);
        return $this->_getPublishedFilePath($relativeFilePath, $params);
    }

    /**
     * Get published file path
     *
     * Check, if requested theme file has public access, and move it to public folder, if the file has no public access
     *
     * @param  string $filePath
     * @param  array $params
     * @return string
     * @throws \Magento\Exception
     */
    protected function _getPublishedFilePath($filePath, $params)
    {
        //TODO: Do we need this? It throws exception in production mode!
        if (!$this->_viewService->isViewFileOperationAllowed()) {
            throw new \Magento\Exception('Filesystem operations are not permitted for view files');
        }

        // 1. Fallback look-up for view files. Remember it can be file of any type: CSS, LESS, JS, image
        $sourcePath = $this->_viewFileSystem->getViewFile($filePath, $params);

        // 2. If $sourcePath returned actually not exists replace it with null value.
        if (!$this->rootDirectory->isExist($this->rootDirectory->getRelativePath($sourcePath))) {
            $sourcePath = null;
        }

        /**
         * 3. Target directory to save temporary files in. It was 'pub/static' dir, but I guess it's more correct
         * to have it in 'var/tmp' dir.
         */
        //TODO: Why should publisher control where pre-processors save temporary files
        $targetDirectory = $this->_filesystem->getDirectoryWrite(\Magento\App\Filesystem::VAR_DIR);

        /**
         * 4. Execute asset pre-processors
         *      in case if $sourcePath was null, then pre-processors will be executed and original source file
         *          will be processed, then new $sourcePath targeting pre-processed file in 'var/tmp' dir
         *          will be returned back
         *      in case if $sourcePath was not null then $sourcePath passed will be returned back
         */
        $sourcePath = $this->preProcessor->process($filePath, $params, $targetDirectory, $sourcePath);

        // 5. If $sourcePath returned still doesn't exists throw Exception
        if ($sourcePath === null
            || !$this->rootDirectory->isExist($this->rootDirectory->getRelativePath($sourcePath))
        ) {
            throw new \Magento\Exception("Unable to locate theme file '{$filePath}'.");
        }

        /**
         * 6.
         * If $sourcePath points to file in 'pub/lib' dir - no publishing required
         * If $sourcePath points to file with protected extension - no publishing, return unchanged
         * If $sourcePath points to file in 'pub/static' dir - no publishing required
         * If $sourcePath points to CSS file and developer mode is enabled - publish file
         */
        if ($this->canSkipFilePublication($sourcePath)) {
            return $sourcePath;
        }

        return $this->_publishFile($filePath, $params, $sourcePath);
    }

    /**
     * Publish file
     *
     * @param string $filePath
     * @param array $params
     * @param string $sourcePath
     * @return string
     */
    protected function _publishFile($filePath, $params, $sourcePath)
    {
        $filePath = $this->_viewFileSystem->normalizePath($filePath);
        $sourcePath = $this->_viewFileSystem->normalizePath($sourcePath);
        $targetPath = $this->_buildPublishedFilePath($filePath, $params, $sourcePath);

        $targetDirectory = $this->_filesystem->getDirectoryWrite(\Magento\App\Filesystem::STATIC_VIEW_DIR);
        $sourcePathRelative = $this->rootDirectory->getRelativePath($sourcePath);
        $targetPathRelative = $targetDirectory->getRelativePath($targetPath);

        if ($this->_getExtension($filePath) == self::CONTENT_TYPE_CSS) {
            $cssContent = $this->_getPublicCssContent($sourcePath, $targetPath, $filePath, $params);
        }

        $fileMTime = $this->rootDirectory->stat($sourcePathRelative)['mtime'];

        if (!$targetDirectory->isExist($targetPathRelative)
            || $fileMTime != $targetDirectory->stat($targetPathRelative)['mtime']
        ) {
            if (isset($cssContent)) {
                $targetDirectory->writeFile($targetPathRelative, $cssContent);
                $targetDirectory->touch($targetPathRelative, $fileMTime);
            } elseif ($this->rootDirectory->isFile($sourcePathRelative)) {
                $this->rootDirectory->copyFile($sourcePathRelative, $targetPathRelative, $targetDirectory);
                $targetDirectory->touch($targetPathRelative, $fileMTime);
            }
        }

        $this->_viewFileSystem->notifyViewFileLocationChanged($targetPath, $filePath, $params);
        return $targetPath;
    }

    /**
     * Build published file path
     *
     * @param string $filePath
     * @param array $params
     * @param string $sourcePath
     * @return string
     */
    protected function _buildPublishedFilePath($filePath, $params, $sourcePath)
    {
        $isCssFile = $this->_getExtension($filePath) == self::CONTENT_TYPE_CSS;
        if ($this->_allowDuplication || $isCssFile) {
            $targetPath = $this->_buildPublicViewRedundantFilename($filePath, $params);
        } else {
            $targetPath = $this->_buildPublicViewSufficientFilename($sourcePath, $params);
        }
        $targetPath = $this->_buildPublicViewFilename($targetPath);

        return $targetPath;
    }

    /**
     * Determine whether a file needs to be published
     *
     * All files located in 'pub/lib' dir should not be published cause it's already publicly accessible.
     * All other files must be processed either if they are not published already (located in 'pub/static'),
     * or if they are css-files and we're working in developer mode.
     *
     * @param string $filePath
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function canSkipFilePublication($filePath)
    {
        $filePath = str_replace('\\', '/', $filePath);

        $pubLibDir = $this->_filesystem->getPath(\Magento\App\Filesystem::PUB_LIB_DIR) . '/';
        if (strncmp($filePath, $pubLibDir, strlen($pubLibDir)) === 0) {
            return true;
        }

        $protectedExtensions = array(
            self::CONTENT_TYPE_PHP,
            self::CONTENT_TYPE_PHTML,
            self::CONTENT_TYPE_XML
        );
        if (in_array($this->_getExtension($filePath), $protectedExtensions)) {
            return true;
        }

        $pubStaticDir = $this->_filesystem->getPath(\Magento\App\Filesystem::STATIC_VIEW_DIR) . '/';
        if (strncmp($filePath, $pubStaticDir, strlen($pubStaticDir)) !== 0) {
            return false;
        }

        if ($this->_viewService->getAppMode() !== \Magento\App\State::MODE_DEVELOPER) {
            return true;
        }

        return $this->_getExtension($filePath) !== self::CONTENT_TYPE_CSS;
    }

    /**
     * Get file extension by file path
     *
     * @param string $filePath
     * @return string
     */
    protected function _getExtension($filePath)
    {
        return strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    }

    /**
     * Build public filename for a theme file that always includes area/package/theme/locate parameters
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    protected function _buildPublicViewRedundantFilename($file, array $params)
    {
        /** @var $theme \Magento\View\Design\ThemeInterface */
        $theme = $params['themeModel'];
        if ($theme->getThemePath()) {
            $designPath = $theme->getThemePath();
        } elseif ($theme->getId()) {
            $designPath = self::PUBLIC_THEME_DIR . $theme->getId();
        } else {
            $designPath = self::PUBLIC_VIEW_DIR;
        }

        $publicFile = $params['area'] . '/' . $designPath . '/' . $params['locale'] .
            ($params['module'] ? '/' . $params['module'] : '') . '/' . $file;

        return $publicFile;
    }

    /**
     * Build public filename for a view file that sufficiently depends on the passed parameters
     *
     * @param string $filename
     * @param array $params
     * @return string
     */
    protected function _buildPublicViewSufficientFilename($filename, array $params)
    {
        $designDir = $this->_filesystem->getPath(\Magento\App\Filesystem::THEMES_DIR) . '/';
        if (0 === strpos($filename, $designDir)) {
            // theme file
            $publicFile = substr($filename, strlen($designDir));
        } else {
            // modular file
            $module = $params['module'];
            $moduleDir = $this->_modulesReader->getModuleDir('theme', $module) . '/';
            $publicFile = substr($filename, strlen($moduleDir));
            $publicFile = self::PUBLIC_MODULE_DIR . '/' . $module . '/' . $publicFile;
        }
        return $publicFile;
    }

    /**
     * Retrieve processed CSS file content that contains URLs relative to the specified public directory
     *
     * @param string $sourcePath Absolute path to the current location of CSS file
     * @param string $publicPath Absolute path to location of the CSS file, where it will be published
     * @param string $fileName File name used for reference
     * @param array $params Design parameters
     * @return string
     */
    protected function _getPublicCssContent($sourcePath, $publicPath, $fileName, $params)
    {
        $content = $this->rootDirectory->readFile($this->rootDirectory->getRelativePath($sourcePath));

        $callback = function ($fileId, $originalPath) use ($fileName, $params) {
            $relatedPathPublic = $this->_publishRelatedViewFile(
                $fileId, $originalPath, $fileName, $params
            );
            return $relatedPathPublic;
        };
        try {
            $content = $this->_cssUrlResolver->replaceCssRelativeUrls($content, $sourcePath, $publicPath, $callback);
        } catch (\Magento\Exception $e) {
            $this->_logger->logException($e);
        }
        return $content;
    }

    /**
     * Build path to file located in public folder
     *
     * @param string $file
     * @return string
     */
    protected function _buildPublicViewFilename($file)
    {
        return $this->_viewService->getPublicDir() . '/' . $file;
    }
}
