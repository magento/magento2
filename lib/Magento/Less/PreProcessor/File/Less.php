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

namespace Magento\Less\PreProcessor\File;

use Magento\View;

/**
 * Less file
 */
class Less
{
    /**
     * Folder for publication preprocessed less files
     */
    const PUBLICATION_PREFIX_PATH = 'less';

    /**
     * @var View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * @var \Magento\App\Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $viewParams;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $sourcePath;

    /**
     * @var bool
     */
    protected $isPublished = false;

    /**
     * @param View\FileSystem $viewFileSystem
     * @param \Magento\App\Filesystem $filesystem
     * @param string $filePath
     * @param array $viewParams
     * @param string|null $sourcePath
     */
    public function __construct(
        View\FileSystem $viewFileSystem,
        \Magento\App\Filesystem $filesystem,
        $filePath,
        array $viewParams,
        $sourcePath = null
    ) {
        $this->viewFileSystem = $viewFileSystem;
        $this->filesystem = $filesystem;
        $this->filePath = $filePath;
        $this->viewParams = $viewParams;
        $this->sourcePath = $sourcePath ?: $this->getSourcePath();
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
     * Return source path of file if it's exist
     *
     * @return string
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function getSourcePath()
    {
        if ($this->sourcePath === null) {
            $this->sourcePath = $this->viewFileSystem->getViewFile($this->getFilePath(), $this->getViewParams());
            if (!$this->getDirectoryRead()->isExist($this->getDirectoryRead()->getRelativePath($this->sourcePath))) {
                throw new \Magento\Filesystem\FilesystemException("File '{$this->sourcePath}' isn't exist");
            }
        }
        return $this->sourcePath;
    }

    /**
     * Build unique file path for publication
     *
     * @return string
     */
    public function getPublicationPath()
    {
        $sourcePathPrefix = $this->getDirectoryRead()->getAbsolutePath();
        $targetPathPrefix = $this->getDirectoryWrite()->getAbsolutePath() . self::PUBLICATION_PREFIX_PATH . '/';
        return str_replace($sourcePathPrefix, $targetPathPrefix, $this->getSourcePath());
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $directoryRead = $this->getDirectoryRead();
        $filePath = $this->isPublished() ? $this->getPublicationPath() : $this->getSourcePath();
        return $directoryRead->readFile($directoryRead->getRelativePath($filePath));
    }

    /**
     * Save file content to publication path
     *
     * @param string $content
     */
    public function saveContent($content)
    {
        $directoryWrite = $this->getDirectoryWrite();
        $directoryWrite->writeFile($directoryWrite->getRelativePath($this->getPublicationPath()), $content);
        $this->isPublished = true;
    }

    /**
     * Publishing state
     *
     * @return bool
     */
    public function isPublished()
    {
        return $this->isPublished;
    }

    /**
     * Unique identifier for a file
     *
     * @return string
     */
    public function getFileIdentifier()
    {
        $themeIdentifier = !empty($this->viewParams['themeModel']) && $this->viewParams['themeModel']->getFullPath()
            ? 'base'
            : $this->viewParams['themeModel']->getFullPath();
        $module = empty($this->viewParams['module']) ? 'base' : $this->viewParams['module'];
        $locale = empty($this->viewParams['locale']) ? 'base' : $this->viewParams['locale'];
        return implode('|', [$this->filePath, $module, $themeIdentifier, $locale]);
    }

    /**
     * Get base directory with source of less files
     *
     * @return \Magento\Filesystem\Directory\ReadInterface
     */
    public function getDirectoryRead()
    {
        return $this->filesystem->getDirectoryRead(\Magento\App\Filesystem::ROOT_DIR);
    }

    /**
     * Get directory for publication temporary less files
     *
     * @return \Magento\Filesystem\Directory\WriteInterface
     */
    public function getDirectoryWrite()
    {
        return $this->filesystem->getDirectoryWrite(\Magento\App\Filesystem::TMP_DIR);
    }
}
