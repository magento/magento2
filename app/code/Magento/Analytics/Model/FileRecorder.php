<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class for the handling of registration a new file for MBI.
 * @since 2.2.0
 */
class FileRecorder
{
    /**
     * Resource for managing FileInfo object.
     *
     * @var FileInfoManager
     * @since 2.2.0
     */
    private $fileInfoManager;

    /**
     * @var FileInfoFactory
     * @since 2.2.0
     */
    private $fileInfoFactory;

    /**
     * Subdirectory path for an encoded file.
     *
     * @var string
     * @since 2.2.0
     */
    private $fileSubdirectoryPath = 'analytics/';

    /**
     * File name of an encoded file.
     *
     * @var string
     * @since 2.2.0
     */
    private $encodedFileName = 'data.tgz';

    /**
     * @var Filesystem
     * @since 2.2.0
     */
    private $filesystem;

    /**
     * @param FileInfoManager $fileInfoManager
     * @param FileInfoFactory $fileInfoFactory
     * @param Filesystem $filesystem
     * @since 2.2.0
     */
    public function __construct(
        FileInfoManager $fileInfoManager,
        FileInfoFactory $fileInfoFactory,
        Filesystem $filesystem
    ) {
        $this->fileInfoManager = $fileInfoManager;
        $this->fileInfoFactory = $fileInfoFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Save new encrypted file, register it and remove old registered file.
     *
     * @param EncodedContext $encodedContext
     * @return bool
     * @since 2.2.0
     */
    public function recordNewFile(EncodedContext $encodedContext)
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        $fileRelativePath = $this->getFileRelativePath();
        $directory->writeFile($fileRelativePath, $encodedContext->getContent());

        $fileInfo = $this->fileInfoManager->load();
        $this->registerFile($encodedContext, $fileRelativePath);
        $this->removeOldFile($fileInfo, $directory);

        return true;
    }

    /**
     * Return relative path to encoded file.
     *
     * @return string
     * @since 2.2.0
     */
    private function getFileRelativePath()
    {
        return $this->fileSubdirectoryPath . hash('sha256', time())
            . '/' . $this->encodedFileName;
    }

    /**
     * Register encoded file.
     *
     * @param EncodedContext $encodedContext
     * @param string $fileRelativePath
     * @return bool
     * @since 2.2.0
     */
    private function registerFile(EncodedContext $encodedContext, $fileRelativePath)
    {
        $newFileInfo = $this->fileInfoFactory->create(
            [
                'path' => $fileRelativePath,
                'initializationVector' => $encodedContext->getInitializationVector(),
            ]
        );
        $this->fileInfoManager->save($newFileInfo);

        return true;
    }

    /**
     * Remove previously registered file.
     *
     * @param FileInfo $fileInfo
     * @param WriteInterface $directory
     * @return bool
     * @since 2.2.0
     */
    private function removeOldFile(FileInfo $fileInfo, WriteInterface $directory)
    {
        if (!$fileInfo->getPath()) {
            return true;
        }

        $directory->delete($fileInfo->getPath());

        $directoryName = dirname($fileInfo->getPath());
        if ($directoryName !== '.') {
            $directory->delete($directoryName);
        }

        return true;
    }
}
