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
 * Handles file publication
 */
namespace Magento\Core\Model\View;

class Publisher implements \Magento\Core\Model\View\PublicFilesManagerInterface
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
     * @var \Magento\Core\Helper\Css
     */
    protected $_cssHelper;

    /**
     * @var \Magento\Core\Model\View\Service
     */
    protected $_viewService;

    /**
     * @var \Magento\Core\Model\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * Indicates how to materialize view files: with or without "duplication"
     *
     * @var bool
     */
    protected $_allowFilesDuplication;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * @var \Magento\Core\Model\Config\Modules\Reader
     */
    protected $_modulesReader;

    /**
     * View files publisher model
     *
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Core\Helper\Css $cssHelper
     * @param \Magento\Core\Model\View\Service $viewService
     * @param \Magento\Core\Model\View\FileSystem $viewFileSystem
     * @param \Magento\App\Dir $dir
     * @param \Magento\Core\Model\Config\Modules\Reader $modulesReader
     * @param $allowFilesDuplication
     */
    public function __construct(
        \Magento\Core\Model\Logger $logger,
        \Magento\Filesystem $filesystem,
        \Magento\Core\Helper\Css $cssHelper,
        \Magento\Core\Model\View\Service $viewService,
        \Magento\Core\Model\View\FileSystem $viewFileSystem,
        \Magento\App\Dir $dir,
        \Magento\Core\Model\Config\Modules\Reader $modulesReader,
        $allowFilesDuplication
    ) {
        $this->_filesystem = $filesystem;
        $this->_cssHelper = $cssHelper;
        $this->_viewService = $viewService;
        $this->_viewFileSystem = $viewFileSystem;
        $this->_dir = $dir;
        $this->_modulesReader = $modulesReader;
        $this->_logger = $logger;
        $this->_allowFilesDuplication = $allowFilesDuplication;
    }

    /**
     * {@inheritdoc}
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

        if (!$this->_filesystem->has($sourcePath)) {
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

        $fileMTime = $this->_filesystem->getMTime($sourcePath);
        if (!$this->_filesystem->has($targetPath) || $fileMTime != $this->_filesystem->getMTime($targetPath)) {
            $publicDir = dirname($targetPath);
            if (!$this->_filesystem->isDirectory($publicDir)) {
                $this->_filesystem->createDirectory($publicDir, 0777);
            }

            if (isset($cssContent)) {
                $this->_filesystem->write($targetPath, $cssContent);
                $this->_filesystem->touch($targetPath, $fileMTime);
            } elseif ($this->_filesystem->isFile($sourcePath)) {
                $this->_filesystem->copy($sourcePath, $targetPath);
                $this->_filesystem->touch($targetPath, $fileMTime);
            } elseif (!$this->_filesystem->isDirectory($targetPath)) {
                $this->_filesystem->createDirectory($targetPath, 0777);
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
        if ($this->_allowFilesDuplication || $isCssFile) {
            $targetPath = $this->_buildPublicViewRedundantFilename($filePath, $params);
        } else {
            $targetPath = $this->_buildPublicViewSufficientFilename($sourcePath, $params);
        }
        $targetPath = $this->_buildPublicViewFilename($targetPath);

        return $targetPath;
    }

    /**
     * Determine whether a file needs to be published.
     * Js files are never processed. All other files must be processed either if they are not published already,
     * or if they are css-files and we're working in developer mode.
     *
     * @param string $filePath
     * @return bool
     */
    protected function _needToProcessFile($filePath)
    {
        $jsPath = $this->_dir->getDir(\Magento\App\Dir::PUB_LIB) . DS;
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

        $themePath = $this->_viewService->getPublicDir() . DS;
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
            $designPath = str_replace('/', DS, $theme->getThemePath());
        } elseif ($theme->getId()) {
            $designPath = self::PUBLIC_THEME_DIR . $theme->getId();
        } else {
            $designPath = self::PUBLIC_VIEW_DIR;
        }

        $publicFile = $params['area'] . DS . $designPath . DS . $params['locale'] .
            ($params['module'] ? DS . $params['module'] : '') . DS . $file;

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
        $designDir = $this->_dir->getDir(\Magento\App\Dir::THEMES) . DS;
        if (0 === strpos($filename, $designDir)) {
            // theme file
            $publicFile = substr($filename, strlen($designDir));
        } else {
            // modular file
            $module = $params['module'];
            $moduleDir = $this->_modulesReader->getModuleDir('theme', $module) . DS;
            $publicFile = substr($filename, strlen($moduleDir));
            $publicFile = self::PUBLIC_MODULE_DIR . DS . $module . DS . $publicFile;
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
        $content = $this->_filesystem->read($sourcePath);

        $callback = function ($fileId, $originalPath) use ($fileName, $params) {
            $relatedPathPublic = $this->_publishRelatedViewFile(
                $fileId, $originalPath, $fileName, $params
            );
            return $relatedPathPublic;
        };
        try {
            $content = $this->_cssHelper->replaceCssRelativeUrls($content, $sourcePath, $publicPath, $callback);
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
        return $this->_viewService->getPublicDir() . DS . $file;
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
        if (strpos($fileId, \Magento\Core\Model\View\Service::SCOPE_SEPARATOR)) {
            $filePath = $this->_viewService->extractScope($fileId, $params);
        } else {
            /* Check if module file overridden on theme level based on _module property and file path */
            if ($params['module'] && strpos($parentFilePath, $this->_dir->getDir(\Magento\App\Dir::THEMES)) === 0) {
                /* Add module directory to relative URL */
                $filePath = dirname($params['module'] . '/' . $parentFileName)
                    . '/' . $fileId;
                $filePath = $this->_filesystem->normalizePath($filePath, true);
                if (strpos($filePath, $params['module']) === 0) {
                    $filePath = ltrim(str_replace($params['module'], '', $filePath), '/');
                } else {
                    $params['module'] = false;
                }
            } else {
                $filePath = $this->_filesystem->normalizePath(dirname($parentFileName) . '/' . $fileId, true);
            }
        }

        return $filePath;
    }
}
