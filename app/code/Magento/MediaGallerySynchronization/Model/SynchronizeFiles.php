<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface;
use Magento\MediaGallerySynchronizationApi\Model\ImportFilesInterface;
use Magento\MediaGallerySynchronizationApi\Api\SynchronizeFilesInterface;
use Magento\MediaGallerySynchronization\Model\Filesystem\GetFileInfo;
use Psr\Log\LoggerInterface;

/**
 * Synchronize files in media storage and media assets database records
 */
class SynchronizeFiles implements SynchronizeFilesInterface
{
    /**
     * Date format
     */
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var GetAssetsByPathsInterface
     */
    private $getAssetsByPaths;

    /**
     * @var File
     */
    private $driver;

    /**
     * @var GetFileInfo
     */
    private $getFileInfo;

    /**
     * @var ImportFilesInterface
     */
    private $importFiles;

    /**
     * @var DateTimeFactory
     */
    private $dateFactory;

    /**
     * @param File $driver
     * @param Filesystem $filesystem
     * @param DateTimeFactory $dateFactory
     * @param LoggerInterface $log
     * @param GetFileInfo $getFileInfo
     * @param GetAssetsByPathsInterface $getAssetsByPaths
     * @param ImportFilesInterface $importFiles
     */
    public function __construct(
        File $driver,
        Filesystem $filesystem,
        DateTimeFactory $dateFactory,
        LoggerInterface $log,
        GetFileInfo $getFileInfo,
        GetAssetsByPathsInterface $getAssetsByPaths,
        ImportFilesInterface $importFiles
    ) {
        $this->driver = $driver;
        $this->filesystem = $filesystem;
        $this->dateFactory = $dateFactory;
        $this->log = $log;
        $this->getFileInfo = $getFileInfo;
        $this->getAssetsByPaths = $getAssetsByPaths;
        $this->importFiles = $importFiles;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $paths): void
    {
        try {
            $this->importFiles->execute($this->getPathsToUpdate($paths));
        } catch (LocalizedException $localizedException) {
            throw $localizedException;
        } catch (\Exception $exception) {
            $this->log->critical($exception);
            throw new LocalizedException(
                __(
                    'Could not import media assets for files: %files',
                    [
                        'files' => implode(', ', $paths)
                    ]
                )
            );
        }
    }

    /**
     * Return existing assets from files
     *
     * @param string[] $paths
     * @return array
     * @throws LocalizedException
     */
    private function getPathsToUpdate(array $paths): array
    {
        $assetPaths = [];

        foreach ($paths as $path) {
            $assetPath = $this->getAssetPath($path);
            $assetPaths[$assetPath] = $assetPath;
        }

        $assets = $this->getAssetsByPaths->execute($assetPaths);

        foreach ($assets as $asset) {
            if ($asset->getUpdatedAt() === $this->getFileModificationTime($asset->getPath())) {
                unset($assetPaths[$asset->getPath()]);
            }
        }

        return $assetPaths;
    }

    /**
     * Retrieve formatted file modification time
     *
     * @param string $path
     * @return string
     */
    private function getFileModificationTime(string $path): string
    {
        return $this->dateFactory->create()->gmtDate(
            self::DATE_FORMAT,
            $this->getFileInfo->execute($this->getMediaDirectory()->getAbsolutePath($path))->getMTime()
        );
    }

    /**
     * Get correct path for media asset
     *
     * @param string $path
     * @return string
     */
    private function getAssetPath(string $path): string
    {
        return $this->driver->getParentDirectory($path) === '.' ? '/' . $path : $path;
    }

    /**
     * Retrieve media directory instance
     *
     * @return ReadInterface
     */
    private function getMediaDirectory(): ReadInterface
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }
}
