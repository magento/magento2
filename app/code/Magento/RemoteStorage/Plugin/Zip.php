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
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\RemoteStorage\Model\Config;

/**
 * @see \Magento\Framework\Archive\Zip
 */
class Zip
{
    /**
     * @var WriteInterface
     */
    private WriteInterface $tmpDirectoryWrite;

    /**
     * @var WriteInterface
     */
    private WriteInterface $remoteDirectoryWrite;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Filesystem $filesystem
     * @param TargetDirectory $targetDirectory
     * @param Config $config
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        TargetDirectory $targetDirectory,
        Config $config
    ) {
        $this->tmpDirectoryWrite = $filesystem->getDirectoryWrite(DirectoryList::TMP);
        $this->remoteDirectoryWrite = $targetDirectory->getDirectoryWrite(DirectoryList::ROOT);
        $this->config = $config;
    }

    /**
     * Wrapper method around \Magento\Framework\Archive\Zip::unpack().
     * Copies file from the remote storage to the tmp directory unpacks it
     * and uploads unpacked file to the remote storage.
     *
     * @param \Magento\Framework\Archive\Zip $subject
     * @param callable $proceed
     * @param string $source
     * @param string $destination
     * @return string
     * @throws FileSystemException|RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundUnpack(
        \Magento\Framework\Archive\Zip $subject,
        callable $proceed,
        $source,
        $destination
    ): string {
        return $this->proceedFileOperation($proceed, $source, $destination);
    }

    /**
     * Wrapper method around \Magento\Framework\Archive\Zip::pack().
     * Copies file from the remote storage to the tmp directory packs it
     * and uploads packed file to the remote storage.
     *
     * @param \Magento\Framework\Archive\Zip $subject
     * @param callable $proceed
     * @param string $source
     * @param string $destination
     * @return string
     * @throws FileSystemException|RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPack(
        \Magento\Framework\Archive\Zip $subject,
        callable $proceed,
        $source,
        $destination
    ): string {
        return $this->proceedFileOperation($proceed, $source, $destination);
    }

    /**
     * Common method for both pack and unpack operations
     *
     * @param callable $proceed
     * @param string $source
     * @param string $destination
     * @return string
     * @throws FileSystemException|RuntimeException
     */
    private function proceedFileOperation(callable $proceed, $source, $destination): string
    {
        if ($this->config->isEnabled()) {
            $tmpPath = $this->copyFileToTmp($source);
            $tmpDestination = $this->getTmpPath($destination);

            $proceed($tmpPath, $tmpDestination);

            $this->tmpDirectoryWrite->getDriver()->rename(
                $tmpDestination,
                $destination,
                $this->remoteDirectoryWrite->getDriver()
            );
            $this->tmpDirectoryWrite->delete($tmpPath);

            return $destination;
        } else {
            return $proceed($source, $destination);
        }
    }

    /**
     * Copies file from remote storage to tmp folder
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
            $tmpPath = $this->getTmpPath($filePath);
            $content = $this->remoteDirectoryWrite->getDriver()->fileGetContents($filePath);
            $filePath = $this->tmpDirectoryWrite->getDriver()->filePutContents($tmpPath, $content) >= 0
                ? $tmpPath
                : $filePath;
        }

        return $filePath;
    }

    /**
     * Returns tmp path for given file
     *
     * @param string $filePath
     * @return string
     */
    private function getTmpPath(string $filePath): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return $this->tmpDirectoryWrite->getAbsolutePath() . basename($filePath);
    }
}
