<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Catalog\Block\Product\Gallery;
use Magento\RemoteStorage\Model\Config;

/**
 * Class to copy the image to tmp folder
 */
class GalleryImageCopy
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
     * @var array
     */
    private $tmpFiles = [];

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
     * Copy file from remote server to tmp directory of Magento to get Image width
     *
     * @param Gallery $subject
     * @param callable $proceed
     * @return string
     * @throws FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetImageWidth(Gallery $subject, callable $proceed): string
    {
        return $this->copyFileToTmp($subject,$proceed);
    }

    /**
     * Move files from storage to tmp folder
     *
     * @param Gallery $subject
     * @param callable $proceed
     * @return string
     * @throws FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function copyFileToTmp(Gallery $subject, callable $proceed): string
    {
        if ($this->config->IsEnabled()) {
            $filePath = $subject->getCurrentImage()->getPath();
            if ($this->fileExistsInTmp($filePath)) {
                return $this->tmpFiles[$filePath];
            }
            $absolutePath = $this->remoteDirectoryWrite->getAbsolutePath($filePath);
            if ($this->remoteDirectoryWrite->isFile($absolutePath)) {
                $this->tmpDirectoryWrite->create();
                $tmpPath = $this->storeTmpName($filePath);
                $content = $this->remoteDirectoryWrite->getDriver()->fileGetContents($filePath);
                $filePath = $this->tmpDirectoryWrite->getDriver()->filePutContents($tmpPath, $content)
                    ? $tmpPath
                    : $filePath;
            }
            return $filePath;
        }
    }

    /**
     * Store created tmp image path
     *
     * @param string $filePath
     * @return string
     */
    private function storeTmpName(string $filePath): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $tmpPath = $this->tmpDirectoryWrite->getAbsolutePath() . basename($filePath);
        $this->tmpFiles[$filePath] = $tmpPath;
        return $tmpPath;
    }

    /**
     * Check is file exist in tmp folder
     *
     * @param string $filePath
     * @return bool
     */
    private function fileExistsInTmp(string $filePath): bool
    {
        return array_key_exists($filePath, $this->tmpFiles);
    }
}
