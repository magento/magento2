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
namespace Magento\View\Publisher;

use Magento\Filesystem\Directory\WriteInterface;

/**
 * Abstract publisher file type
 */
abstract class FileAbstract implements FileInterface
{
    /**
     * @var \Magento\App\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\View\Service
     */
    protected $viewService;

    /**
     * @var \Magento\Module\Dir\Reader
     */
    protected $modulesReader;

    /**
     * @var \Magento\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var array
     */
    protected $viewParams;

    /**
     * @var string|null
     */
    protected $sourcePath;

    /**
     * Indicates how to materialize view files: with or without "duplication"
     *
     * @var bool
     */
    protected $allowDuplication;

    /**
     * @var bool
     */
    protected $isPublicationAllowed;

    /**
     * @var WriteInterface
     */
    protected $rootDirectory;

    /**
     * Makes sure that fallback is only used once per file and only if no 'valid' source path was passed to constructor
     *
     * @var bool
     */
    protected $isFallbackUsed = false;

    /**
     * Makes sure that source path is not overwritten when 'valid' value of source path was passed to constructor
     *
     * @var bool
     */
    protected $isSourcePathProvided;

    /**
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\View\Service $viewService
     * @param \Magento\Module\Dir\Reader $modulesReader
     * @param \Magento\View\FileSystem $viewFileSystem
     * @param string $filePath
     * @param bool $allowDuplication
     * @param array $viewParams
     * @param string|null $sourcePath
     */
    public function __construct(
        \Magento\App\Filesystem $filesystem,
        \Magento\View\Service $viewService,
        \Magento\Module\Dir\Reader $modulesReader,
        \Magento\View\FileSystem $viewFileSystem,
        $filePath,
        $allowDuplication,
        array $viewParams,
        $sourcePath = null
    ) {
        $this->filesystem = $filesystem;
        $this->viewService = $viewService;
        $this->modulesReader = $modulesReader;
        $this->filePath = $filePath;
        $this->allowDuplication = $allowDuplication;
        $this->viewParams = $viewParams;
        $this->viewFileSystem = $viewFileSystem;
        $this->rootDirectory = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::ROOT_DIR);
        $this->setSourcePath($sourcePath);
        $this->isSourcePathProvided = $sourcePath !== null;
    }

    /**
     * Determine whether a file needs to be published
     *
     * All files located in 'pub/lib' dir should not be published cause it's already publicly accessible.
     * All other files must be processed either if they are not published already (located in 'pub/static'),
     * or if they are css-files and we're working in developer mode.
     *
     * If sourcePath points to file in 'pub/lib' dir - no publishing required
     * If sourcePath points to file in 'pub/static' dir - no publishing required
     *
     * @return bool
     */
    abstract public function isPublicationAllowed();

    /**
     * Build unique file path for publication
     *
     * @return string
     */
    abstract public function buildUniquePath();

    /**
     * Original file extension
     *
     * @return string
     */
    public function getExtension()
    {
        if ($this->extension === null) {
            $this->extension = strtolower(pathinfo($this->getFilePath(), PATHINFO_EXTENSION));
        }
        return $this->extension;
    }

    /**
     * @return bool
     */
    public function isSourceFileExists()
    {
        return $this->getSourcePath() !== null;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return array
     */
    public function getViewParams()
    {
        return $this->viewParams;
    }

    /**
     * Build path to file located in public folder
     *
     * @return string
     */
    public function buildPublicViewFilename()
    {
        return $this->viewService->getPublicDir() . '/' . $this->buildUniquePath();
    }

    /**
     * @return string|null
     */
    public function getSourcePath()
    {
        if (!$this->isSourcePathProvided && !$this->isFallbackUsed) {
            $this->isFallbackUsed = true;

            // Fallback look-up for view files. Remember it can be file of any type: CSS, LESS, JS, image
            $fallbackSourcePath = $this->viewFileSystem->getViewFile($this->getFilePath(), $this->getViewParams());
            $this->setSourcePath($fallbackSourcePath);
        }
        return $this->sourcePath;
    }

    /**
     * @param string $sourcePath
     * @return $this
     */
    protected function setSourcePath($sourcePath)
    {
        if ($sourcePath === null || !$this->rootDirectory->isExist($this->rootDirectory->getRelativePath($sourcePath))
        ) {
            $this->sourcePath = null;
        } else {
            $this->sourcePath = $sourcePath;
        }

        return $this;
    }

    /**
     * @param string $filePath
     * @return bool
     */
    protected function isLibFile($filePath)
    {
        $pubLibDir = $this->filesystem->getPath(\Magento\App\Filesystem::PUB_LIB_DIR) . '/';
        if (strncmp($filePath, $pubLibDir, strlen($pubLibDir)) === 0) {
            return true;
        }
        return false;
    }

    /**
     * @param string $filePath
     * @return bool
     */
    protected function isViewStaticFile($filePath)
    {
        $pubStaticDir = $this->filesystem->getPath(\Magento\App\Filesystem::STATIC_VIEW_DIR) . '/';
        if (strncmp($filePath, $pubStaticDir, strlen($pubStaticDir)) === 0) {
            return true;
        }
        return false;
    }

    /**
     * Build public filename for a theme file that always includes area/package/theme/locate parameters
     *
     * @return string
     */
    protected function buildPublicViewRedundantFilename()
    {
        /** @var $theme \Magento\View\Design\ThemeInterface */
        $theme = $this->getViewParams()['themeModel'];
        if ($theme->getThemePath()) {
            $designPath = $theme->getThemePath();
        } elseif ($theme->getId()) {
            $designPath = self::PUBLIC_THEME_DIR . $theme->getId();
        } else {
            $designPath = self::PUBLIC_VIEW_DIR;
        }

        $publicFile = $this->getViewParams()['area'] .
            '/' .
            $designPath .
            '/' .
            $this->getViewParams()['locale'] .
            ($this->getViewParams()['module'] ? '/' .
            $this->getViewParams()['module'] : '') .
            '/' .
            $this->getFilePath();

        return $publicFile;
    }

    /**
     * Build public filename for a view file that sufficiently depends on the passed parameters
     *
     * @return string
     */
    protected function buildPublicViewSufficientFilename()
    {
        $designDir = $this->filesystem->getPath(\Magento\App\Filesystem::THEMES_DIR) . '/';
        if (0 === strpos($this->getSourcePath(), $designDir)) {
            // theme file
            $publicFile = substr($this->getSourcePath(), strlen($designDir));
        } else {
            // modular file
            $module = $this->getViewParams()['module'];
            $moduleDir = $this->modulesReader->getModuleDir('theme', $module) . '/';
            $publicFile = substr($this->getSourcePath(), strlen($moduleDir));
            $publicFile = self::PUBLIC_MODULE_DIR . '/' . $module . '/' . $publicFile;
        }
        return $publicFile;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        if (!empty($this->viewParams['themeModel'])) {
            $this->viewParams['themeId'] = $this->viewParams['themeModel']->getId();
            unset($this->viewParams['themeModel']);
        }

        return array(
            'filePath',
            'extension',
            'viewParams',
            'sourcePath',
            'allowDuplication',
            'isPublicationAllowed',
            'isFallbackUsed',
            'isSourcePathProvided'
        );
    }

    /**
     * @return void
     */
    public function __wakeup()
    {
        $objectManager = \Magento\App\ObjectManager::getInstance();
        $this->filesystem = $objectManager->get('\Magento\App\Filesystem');
        $this->viewService = $objectManager->get('\Magento\View\Service');
        $this->modulesReader = $objectManager->get('\Magento\Module\Dir\Reader');
        $this->viewFileSystem = $objectManager->get('\Magento\View\FileSystem');

        $this->viewService->updateDesignParams($this->viewParams);

        $this->rootDirectory = $this->filesystem->getDirectoryWrite(\Magento\App\Filesystem::ROOT_DIR);
    }
}
