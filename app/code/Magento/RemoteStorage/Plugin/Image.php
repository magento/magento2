<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\Adapter\AbstractAdapter;
use Magento\RemoteStorage\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * @see AbstractAdapter
 */
class Image
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $tmpDirectoryWrite;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $targetDirectoryWrite;

    /**
     * @var array
     */
    private $tmpFiles = [];

    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var File
     */
    private $ioFile;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Filesystem $filesystem
     * @param File $ioFile
     * @param TargetDirectory $targetDirectory
     * @param Config $config
     * @param LoggerInterface $logger
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function __construct(
        Filesystem $filesystem,
        File $ioFile,
        TargetDirectory $targetDirectory,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->tmpDirectoryWrite = $filesystem->getDirectoryWrite(DirectoryList::TMP);
        $this->targetDirectoryWrite = $targetDirectory->getDirectoryWrite(DirectoryList::ROOT);
        $this->isEnabled = $config->isEnabled();
        $this->ioFile = $ioFile;
        $this->logger = $logger;
    }

    /**
     * Copy file from remote server to tmp directory of Magento
     *
     * @param AbstractAdapter $subject
     * @param string $filename
     * @return array
     * @throws FileSystemException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeOpen(AbstractAdapter $subject, $filename): array
    {
        if ($this->isEnabled) {
            $filename = $this->copyFileToTmp($filename);
        }
        return [$filename];
    }

    /**
     * Get filesystem tmp path for file and provide it to save() function
     *
     * @param AbstractAdapter $subject
     * @param callable $proceed
     * @param string|null $destination
     * @param string|null $newName
     * @return void
     * @throws FileSystemException
     */
    public function aroundSave(
        AbstractAdapter $subject,
        callable $proceed,
        $destination = null,
        $newName = null
    ): void {
        if ($this->isEnabled) {
            $relativePath = $this->targetDirectoryWrite->getRelativePath($destination);
            $tmpPath = $this->tmpDirectoryWrite->getAbsolutePath($relativePath);

            $proceed($tmpPath, $newName);

            $destination = $this->prepareDestination($subject, $destination, $newName);
            $this->tmpDirectoryWrite->getDriver()->rename(
                $tmpPath,
                $destination,
                $this->targetDirectoryWrite->getDriver()
            );
        } else {
            $proceed($destination, $newName);
        }
    }

    /**
     * Remove created tmp files
     */
    public function __destruct()
    {
        try {
            foreach ($this->tmpFiles as $tmpFile) {
                $this->tmpDirectoryWrite->delete($tmpFile);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Move files from storage to tmp folder
     *
     * @param string $filePath
     * @return string
     * @throws FileSystemException
     */
    private function copyFileToTmp($filePath): string
    {
        $absolutePath = $this->targetDirectoryWrite->getAbsolutePath($filePath);
        if ($this->targetDirectoryWrite->isFile($absolutePath)) {
            $this->tmpDirectoryWrite->create();
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $tmpPath = $this->tmpDirectoryWrite->getAbsolutePath() . basename($filePath);
            $this->storeTmpName($tmpPath);
            $content = $this->targetDirectoryWrite->getDriver()->fileGetContents($filePath);
            $filePath = $this->tmpDirectoryWrite->getDriver()->filePutContents($tmpPath, $content)
                ? $tmpPath
                : $filePath;
        }
        return $filePath;
    }

    /**
     * Store created tmp image path
     *
     * @param string $path
     */
    private function storeTmpName(string $path): void
    {
        $this->tmpFiles[] = $path;
    }

    /**
     * Prepare destination path
     *
     * @param AbstractAdapter $image
     * @param string|null $destination
     * @param string|null $newName
     * @return string
     */
    private function prepareDestination(
        AbstractAdapter $image,
        string $destination = null,
        string $newName = null
    ): string {
        if (empty($destination)) {
            $destination = $image->getFileSrcPath();
        } elseif (empty($newName)) {
            $info = $this->ioFile->getPathInfo($destination);
            $newName = $info['basename'];
            $destination = $info['dirname'];
        }

        if (empty($newName)) {
            $newFileName = $image->getFileSrcName();
        } else {
            $newFileName = $newName;
        }
        return rtrim($destination, '/') . '/' . $newFileName;
    }
}
