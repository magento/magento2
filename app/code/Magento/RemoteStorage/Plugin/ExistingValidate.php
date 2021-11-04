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
use Magento\Framework\Image\Adapter\AbstractAdapter;
use Magento\RemoteStorage\Model\Config;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product\Option\Type\File\ExistingValidate as Subject;

/**
 * @see AbstractAdapter
 */
class ExistingValidate
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
     * @param string $value
     * @param string|null $originalName
     * @return array
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeIsValid(Subject $subject, $value, string $originalName = null)
    {
        if ($this->isEnabled) {
            $value = $this->copyFileToTmp($value);
        }
        return [$value, $originalName];
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
