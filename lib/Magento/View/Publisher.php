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

/**
 * Magento view file publisher
 */
class Publisher implements PublicFilesManagerInterface
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

    /**
     * View file system
     *
     * @var \Magento\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * Pre-processor
     *
     * @var \Magento\View\Asset\PreProcessor\PreProcessorInterface
     */
    protected $preProcessor;

    /**
     * Publisher file factory
     *
     * @var Publisher\FileFactory
     */
    protected $fileFactory;

    /**
     * Root directory
     *
     * @var WriteInterface
     */
    protected $rootDirectory;

    /**
     * Pre-processors temporary directory
     *
     * @var WriteInterface
     */
    protected $tmpDirectory;

    /**
     * Public directory
     *
     * @var WriteInterface
     */
    protected $pubDirectory;

    /**
     * @param \Magento\App\Filesystem $filesystem
     * @param FileSystem $viewFileSystem
     * @param Asset\PreProcessor\PreProcessorInterface $preProcessor
     * @param Publisher\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\App\Filesystem $filesystem,
        \Magento\View\FileSystem $viewFileSystem,
        \Magento\View\Asset\PreProcessor\PreProcessorInterface $preProcessor,
        Publisher\FileFactory $fileFactory
    ) {
        $this->rootDirectory = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::ROOT_DIR);
        $this->tmpDirectory = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::VAR_DIR);
        $this->pubDirectory = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::STATIC_VIEW_DIR);
        $this->viewFileSystem = $viewFileSystem;
        $this->preProcessor = $preProcessor;
        $this->fileFactory = $fileFactory;
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
        return $this->getPublishedFilePath($this->fileFactory->create($filePath, $params));
    }

    /**
     * @param string $extension
     * @return bool
     */
    protected function isAllowedExtension($extension)
    {
        $protectedExtensions = array(
            self::CONTENT_TYPE_PHP,
            self::CONTENT_TYPE_PHTML,
            self::CONTENT_TYPE_XML
        );
        if (in_array($extension, $protectedExtensions)) {
            return false;
        }
        return true;
    }

    /**
     * Get published file path
     *
     * Check, if requested theme file has public access, and move it to public folder, if the file has no public access
     *
     * @param Publisher\FileInterface $publisherFile
     * @return string|null
     * @throws \Magento\Exception
     */
    protected function getPublishedFilePath(Publisher\FileInterface $publisherFile)
    {
        /** If $filePath points to file with protected extension - no publishing, return null */
        if (!$this->isAllowedExtension($publisherFile->getExtension())) {
            return null;
        }

        $fileToPublish = $this->preProcessor->process($publisherFile, $this->tmpDirectory);

        if (!$fileToPublish->isSourceFileExists()) {
            throw new \Magento\Exception("Unable to locate theme file '{$fileToPublish->getFilePath()}'.");
        }

        if (!$fileToPublish->isPublicationAllowed()) {
            return $fileToPublish->getSourcePath();
        }

        $this->publishFile($fileToPublish);
        return $fileToPublish->buildPublicViewFilename();
    }

    /**
     * Publish file
     *
     * @param Publisher\FileInterface $publisherFile
     * @return $this
     */
    protected function publishFile(Publisher\FileInterface $publisherFile)
    {
        $sourcePath = $publisherFile->getSourcePath();
        $sourcePathRelative = $this->rootDirectory->getRelativePath($sourcePath);

        $targetPathRelative = $publisherFile->buildUniquePath();

        $targetDirectory = $this->pubDirectory;

        $fileMTime = $this->rootDirectory->stat($sourcePathRelative)['mtime'];
        if (!$targetDirectory->isExist($targetPathRelative)
            || $fileMTime != $targetDirectory->stat($targetPathRelative)['mtime']
        ) {
            if ($this->rootDirectory->isFile($sourcePathRelative)) {
                $this->rootDirectory->copyFile($sourcePathRelative, $targetPathRelative, $targetDirectory);
                $targetDirectory->touch($targetPathRelative, $fileMTime);
            } elseif (!$targetDirectory->isDirectory($targetPathRelative)) {
                $targetDirectory->create($targetPathRelative);
            }
        }

        $this->viewFileSystem->notifyViewFileLocationChanged($publisherFile);
        return $this;
    }
}
