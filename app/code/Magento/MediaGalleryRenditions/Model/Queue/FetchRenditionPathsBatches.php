<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaGalleryRenditions\Model\Queue;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;

/**
 * Fetch files from media storage in batches
 */
class FetchRenditionPathsBatches
{
    private const RENDITIONS_DIRECTORY_NAME = '.renditions';

    /**
     * @var GetFilesIterator
     */
    private $getFilesIterator;

    /**
     * @var Filesystem
     */
    private $filesystem;

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
     * @param Filesystem $filesystem
     * @param GetFilesIterator $getFilesIterator
     * @param int $batchSize
     * @param array $fileExtensions
     */
    public function __construct(
        LoggerInterface $log,
        Filesystem $filesystem,
        GetFilesIterator $getFilesIterator,
        int $batchSize,
        array $fileExtensions
    ) {
        $this->log = $log;
        $this->getFilesIterator = $getFilesIterator;
        $this->filesystem = $filesystem;
        $this->batchSize = $batchSize;
        $this->fileExtensions = $fileExtensions;
    }

    /**
     * Return files from files system by provided size of batch
     */
    public function execute(): \Traversable
    {
        $index = 0;
        $batch = [];
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $iterator = $this->getFilesIterator->execute(
            $mediaDirectory->getAbsolutePath(self::RENDITIONS_DIRECTORY_NAME)
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            $relativePath = $mediaDirectory->getRelativePath($file->getPathName());
            if (!$this->isApplicable($relativePath)) {
                continue;
            }

            $batch[] = $relativePath;
            if (++$index == $this->batchSize) {
                yield $batch;
                $index = 0;
                $batch = [];
            }
        }
        if (count($batch) > 0) {
            yield $batch;
        }
    }

    /**
     * Is the path a valid image path
     *
     * @param string $path
     * @return bool
     */
    private function isApplicable(string $path): bool
    {
        try {
            return $path && preg_match('#\.(' . implode("|", $this->fileExtensions) . ')$# i', $path);
        } catch (\Exception $exception) {
            $this->log->critical($exception);
            return false;
        }
    }
}
