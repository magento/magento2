<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaGallerySynchronization\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Psr\Log\LoggerInterface;

/**
 * Fetch files from media storage in batches
 */
class FetchMediaStorageFileBatches
{
    /**
     * @var GetAssetsIterator
     */
    private $getAssetsIterator;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IsPathExcludedInterface
     */
    private $isPathExcluded;

    /**
     * @var File
     */
    private $driver;

    /**
     * @var string
     */
    private $fileExtensions;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param LoggerInterface $log
     * @param IsPathExcludedInterface $isPathExcluded
     * @param Filesystem $filesystem
     * @param GetAssetsIterator $assetsIterator
     * @param File $driver
     * @param int $batchSize
     * @param array $fileExtensions
     */
    public function __construct(
        LoggerInterface $log,
        IsPathExcludedInterface $isPathExcluded,
        Filesystem $filesystem,
        GetAssetsIterator $assetsIterator,
        File $driver,
        int $batchSize,
        array $fileExtensions
    ) {
        $this->log = $log;
        $this->isPathExcluded = $isPathExcluded;
        $this->getAssetsIterator = $assetsIterator;
        $this->filesystem = $filesystem;
        $this->driver = $driver;
        $this->batchSize = $batchSize;
        $this->fileExtensions = $fileExtensions;
    }

    /**
     * Return files from files system by provided size of batch
     */
    public function execute(): \Traversable
    {
        $i = 0;
        $batch = [];
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        /** @var \SplFileInfo $file */
        foreach ($this->getAssetsIterator->execute($mediaDirectory->getAbsolutePath()) as $file) {
            $relativePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
                ->getRelativePath($file->getPathName());
            if (!$this->isApplicable($relativePath)) {
                continue;
            }

            $batch[] = $relativePath;
            if (++$i == $this->batchSize) {
                yield $batch;
                $i = 0;
                $batch = [];
            }
        }
        if (count($batch) > 0) {
            yield $batch;
        }
    }

    /**
     * Can synchronization be applied to asset with provided path
     *
     * @param string $path
     * @return bool
     */
    private function isApplicable(string $path): bool
    {
        try {
            return $path
                && !$this->isPathExcluded->execute($path)
                && preg_match('#\.(' . implode("|", $this->fileExtensions) . ')$# i', $path);
        } catch (\Exception $exception) {
            $this->log->critical($exception);
            return false;
        }
    }
}
