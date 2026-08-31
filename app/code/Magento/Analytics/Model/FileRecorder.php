<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;

/**
 * Class for the handling of registration a new file for MBI.
 */
class FileRecorder
{
    /**
     * Resource for managing FileInfo object.
     *
     * @var FileInfoManager
     */
    private $fileInfoManager;

    /**
     * @var FileInfoFactory
     */
    private $fileInfoFactory;

    /**
     * Subdirectory path for an encoded file.
     *
     * @var string
     */
    private $fileSubdirectoryPath = 'analytics/';

    /**
     * File name of an encoded file.
     *
     * @var string
     */
    private $encodedFileName = 'data.tgz';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param FileInfoManager $fileInfoManager
     * @param FileInfoFactory $fileInfoFactory
     * @param Filesystem $filesystem
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
     * Stream a new encrypted file to storage, register it and remove the old registered file.
     *
     * The destination file handle is passed to the given writer, which streams the encrypted content
     * into it and returns the resulting EncodedContext with the initialization vector. This avoids
     * holding the whole file content in memory.
     *
     * @param callable $writer function(FileWriteInterface $file): EncodedContext
     * @return bool
     * @throws FileSystemException
     */
    public function recordNewFileStreamed(callable $writer): bool
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        $fileRelativePath = $this->getFileRelativePath();
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $directory->create(dirname($fileRelativePath));
        $file = $directory->openFile($fileRelativePath, 'w+');
        try {
            /** @var EncodedContext $encodedContext */
            $encodedContext = $writer($file);
        } catch (\Throwable $e) {
            // Close before deleting so cleanup never runs against an open handle.
            $file->close();
            $file = null;
            try {
                $directory->delete($fileRelativePath);
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $directoryName = dirname($fileRelativePath);
                if ($directoryName !== '.') {
                    $directory->delete($directoryName);
                }
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (\Throwable $cleanupError) {
                // Cleanup failure must not mask the original error.
            }
            throw $e;
        } finally {
            if ($file !== null) {
                $file->close();
            }
        }

        $fileInfo = $this->fileInfoManager->load();
        $this->registerFile($encodedContext, $fileRelativePath);
        $this->removeOldFile($fileInfo, $directory);

        return true;
    }

    /**
     * Return relative path to encoded file.
     *
     * @return string
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
     */
    private function removeOldFile(FileInfo $fileInfo, WriteInterface $directory)
    {
        if (!$fileInfo->getPath()) {
            return true;
        }

        $directory->delete($fileInfo->getPath());
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $directoryName = dirname($fileInfo->getPath());
        if ($directoryName !== '.') {
            $directory->delete($directoryName);
        }

        return true;
    }
}
