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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * Helper to process css content
     *
     * @var \Magento\View\Url\CssResolver
     */
    protected $_cssUrlResolver;

    /**
     * @var \Magento\View\Service
     */
    protected $_viewService;

    /**
     * @var \Magento\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
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
     * @var \Magento\Module\Dir\Reader
     */
    protected $_modulesReader;

    /**
     * @var WriteInterface
     */
    protected $rootDirectory;

    /**
     * @param \Magento\Logger $logger
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\View\Url\CssResolver $cssUrlResolver
     * @param Service $viewService
     * @param FileSystem $viewFileSystem
     * @param \Magento\Module\Dir\Reader $modulesReader
     * @param $allowDuplication
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\Filesystem $filesystem,
        \Magento\View\Url\CssResolver $cssUrlResolver,
        \Magento\View\Service $viewService,
        \Magento\View\FileSystem $viewFileSystem,
        \Magento\Module\Dir\Reader $modulesReader,
        $allowDuplication
    ) {
        $this->_filesystem = $filesystem;
        $this->rootDirectory = $filesystem->getDirectoryWrite(\Magento\Filesystem::ROOT);
        $this->_cssUrlResolver = $cssUrlResolver;
        $this->_viewService = $viewService;
        $this->_viewFileSystem = $viewFileSystem;
        $this->_modulesReader = $modulesReader;
        $this->_logger = $logger;
        $this->_allowDuplication = $allowDuplication;
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
        $relativeFilePath = $this->_getRelatedViewFile($fileId, $parentFilePath, $parentFileName, $params);
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
        if (!$this->_viewService->isViewFileOperationAllowed()) {
            throw new \Magento\Exception('Filesystem operations are not permitted for view files');
        }

        $sourcePath = $this->_viewFileSystem->getViewFile($filePath, $params);

        if (!$this->rootDirectory->isExist($this->rootDirectory->getRelativePath($sourcePath))) {
            throw new \Magento\Exception("Unable to locate theme file '{$sourcePath}'.");
        }
        if (!$this->_needToProcessFile($sourcePath)) {
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
        $targetPath = $this->_buildPublishedFilePath($filePath, $params, $sourcePath);

        /* Validate whether file needs to be published */
        $isCssFile = $this->_getExtension($filePath) == self::CONTENT_TYPE_CSS;
        if ($isCssFile) {
            $cssContent = $this->_getPublicCssContent($sourcePath, $targetPath, $filePath, $params);
        }

        $targetDirectory = $this->_filesystem->getDirectoryWrite(\Magento\Filesystem::STATIC_VIEW);
        $sourcePathRelative = $this->rootDirectory->getRelativePath($sourcePath);
        $targetPathRelative = $targetDirectory->getRelativePath($targetPath);

        $fileMTime = $this->rootDirectory->stat($sourcePathRelative)['mtime'];
        if (!$targetDirectory->isExist($targetPathRelative)
            || $fileMTime != $targetDirectory->stat($targetPathRelative)['mtime']) {
            if (isset($cssContent)) {
                $targetDirectory->writeFile($targetPathRelative, $cssContent);
                $targetDirectory->touch($targetPathRelative, $fileMTime);
            } elseif ($this->rootDirectory->isFile($sourcePathRelative)) {
                $this->rootDirectory->copyFile($sourcePathRelative, $targetPathRelative, $targetDirectory);
                $targetDirectory->touch($targetPathRelative, $fileMTime);
            } elseif (!$targetDirectory->isDirectory($targetPathRelative)) {
                $targetDirectory->create($targetPathRelative);
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
     * Js files are never processed. All other files must be processed either if they are not published already,
     * or if they are css-files and we're working in developer mode.
     *
     * @param string $filePath
     * @return bool
     */
    protected function _needToProcessFile($filePath)
    {
        $jsPath = $this->_filesystem->getPath(\Magento\Filesystem::PUB_LIB) . '/';
        $filePath = str_replace('\\', '/', $filePath);
        if (strncmp($filePath, $jsPath, strlen($jsPath)) === 0) {
            return false;
        }

        $protectedExtensions = array(
            self::CONTENT_TYPE_PHP,
            self::CONTENT_TYPE_PHTML,
            self::CONTENT_TYPE_XML
        );
        if (in_array($this->_getExtension($filePath), $protectedExtensions)) {
            return false;
        }

        $themePath = $this->_filesystem->getPath(\Magento\Filesystem::STATIC_VIEW) . '/';
        if (strncmp($filePath, $themePath, strlen($themePath)) !== 0) {
            return true;
        }

        return ($this->_viewService->getAppMode() == \Magento\App\State::MODE_DEVELOPER)
            && $this->_getExtension($filePath) == self::CONTENT_TYPE_CSS;
    }

    /**
     * Get file extension by file path
     *
     * @param string $filePath
     * @return string
     */
    protected function _getExtension($filePath)
    {
        $dotPosition = strrpos($filePath, '.');
        return strtolower(substr($filePath, $dotPosition + 1));
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
        $designDir = $this->_filesystem->getPath(\Magento\Filesystem::THEMES) . '/';
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

    /**
     * Get relative $fileUrl based on information about parent file path and name.
     *
     * @param string $fileId URL to the file that was extracted from $parentFilePath
     * @param string $parentFilePath path to the file
     * @param string $parentFileName original file name identifier that was requested for processing
     * @param array $params theme/module parameters array
     * @return string
     */
    protected function _getRelatedViewFile($fileId, $parentFilePath, $parentFileName, &$params)
    {
        if (strpos($fileId, \Magento\View\Service::SCOPE_SEPARATOR)) {
            $filePath = $this->_viewService->extractScope($fileId, $params);
        } else {
            /* Check if module file overridden on theme level based on _module property and file path */
            $themesPath = $this->_filesystem->getPath(\Magento\Filesystem::THEMES);
            if ($params['module'] && strpos($parentFilePath, $themesPath) === 0) {
                /* Add module directory to relative URL */
                $filePath = dirname($params['module'] . '/' . $parentFileName)
                    . '/' . $fileId;
                if (strpos($filePath, $params['module']) === 0) {
                    $filePath = ltrim(str_replace($params['module'], '', $filePath), '/');
                } else {
                    $params['module'] = false;
                }
            } else {
                $filePath = dirname($parentFileName) . '/' . $fileId;
            }
        }

        return $filePath;
    }
}
