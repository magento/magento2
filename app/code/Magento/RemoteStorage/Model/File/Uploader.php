<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\DriverPool;
use Magento\RemoteStorage\Model\Config;

/**
 * Uploader class for cases when remote storage is enabled and the uploaded file is located on remote storage
 */
class Uploader extends \Magento\Framework\File\Uploader
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $tmpDirectoryWrite;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $remoteDirectoryWrite;

    /**
     * Copies file to the tmp directory if remote storage is enabled and tmp file is located on remote storage
     *
     * @param string|array $fileId
     * @param Mime|null $fileMime
     * @param DirectoryList|null $directoryList
     * @param DriverPool|null $driverPool
     * @param TargetDirectory|null $targetDirectory
     * @param Filesystem|null $filesystem
     * @param Config|null $config
     * @throws \DomainException
     */
    public function __construct(
        $fileId,
        ?Mime $fileMime = null,
        ?DirectoryList $directoryList = null,
        ?DriverPool $driverPool = null,
        ?TargetDirectory $targetDirectory = null,
        ?Filesystem $filesystem = null,
        ?Config $config = null
    ) {
        $targetDirectory = $targetDirectory ?: ObjectManager::getInstance()->get(TargetDirectory::class);
        $filesystem = $filesystem ?: ObjectManager::getInstance()->get(FileSystem::class);
        $config = $config ?: ObjectManager::getInstance()->get(Config::class);

        if ($config->isEnabled() && isset($fileId['tmp_name'])) {
            $this->tmpDirectoryWrite = $filesystem->getDirectoryWrite(DirectoryList::TMP);
            $this->remoteDirectoryWrite = $targetDirectory->getDirectoryWrite(DirectoryList::ROOT);

            $fileId['tmp_name'] = $this->copyFileToTmp($fileId['tmp_name']);
        }

        parent::__construct($fileId, $fileMime, $directoryList, $driverPool, $targetDirectory, $filesystem);
    }

    /**
     * Moves file from the remote storage to the tmp folder
     *
     * @param string $filePath
     * @return string
     * @throws FileSystemException
     */
    private function copyFileToTmp(string $filePath): string
    {
        $absolutePath = $this->remoteDirectoryWrite->getAbsolutePath($filePath);
        if ($this->remoteDirectoryWrite->isFile($absolutePath)) {
            $this->tmpDirectoryWrite->create();
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $tmpPath = $this->tmpDirectoryWrite->getAbsolutePath() . basename($filePath);
            $content = $this->remoteDirectoryWrite->getDriver()->fileGetContents($filePath);
            $this->tmpDirectoryWrite->getDriver()->filePutContents($tmpPath, $content);

            return $tmpPath;
        }

        return $filePath;
    }
}
