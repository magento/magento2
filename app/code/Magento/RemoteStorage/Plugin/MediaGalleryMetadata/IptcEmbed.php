<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Plugin\MediaGalleryMetadata;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\MediaGalleryMetadata\Model\IptcEmbed as Subject;
use Magento\RemoteStorage\Model\Config;
use Psr\Log\LoggerInterface;

/**
 *
 */
class IptcEmbed
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
     * @var bool
     */
    private $isEnabled;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Filesystem $filesystem
     * @param TargetDirectory $targetDirectory
     * @param Config $config
     * @param LoggerInterface $logger
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function __construct(
        Filesystem $filesystem,
        TargetDirectory $targetDirectory,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->tmpDirectoryWrite = $filesystem->getDirectoryWrite(DirectoryList::TMP);
        $this->remoteDirectoryWrite = $targetDirectory->getDirectoryWrite(DirectoryList::ROOT);
        $this->isEnabled = $config->isEnabled();
        $this->logger = $logger;
    }

    /**
     * Copies file from the remote server to the tmp directory
     *
     * @param Subject $subject
     * @param string $iptcData
     * @param string $filePath
     * @return array
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGet(Subject $subject, string $iptcData, string $filePath)
    {
        if ($this->isEnabled) {
            $filePath = $this->copyFileToTmp($filePath);
        }

        return [$iptcData, $filePath];
    }

    /**
     * Remove created tmp files
     */
    public function __destruct()
    {
        try {
            foreach ($this->tmpFiles as $key => $tmpFile) {
                $this->tmpDirectoryWrite->delete($tmpFile);
                unset($this->tmpFiles[$key]);
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
    private function copyFileToTmp(string $filePath): string
    {
        if (isset($this->tmpFiles[$filePath])) {
            return $this->tmpFiles[$filePath];
        }

        $absolutePath = $this->remoteDirectoryWrite->getAbsolutePath($filePath);
        if ($this->remoteDirectoryWrite->isFile($absolutePath)) {
            $this->tmpDirectoryWrite->create();
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $tmpPath = $this->tmpDirectoryWrite->getAbsolutePath() . basename($filePath);
            $content = $this->remoteDirectoryWrite->getDriver()->fileGetContents($filePath);
            if ($this->tmpDirectoryWrite->getDriver()->filePutContents($tmpPath, $content) >= 0) {
                $filePath = $tmpPath;
                $this->tmpFiles[$tmpPath] = $tmpPath;
            }
        }
        return $filePath;
    }
}
