<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Gif;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\MediaGalleryMetadata\Model\SegmentNames;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\WriteFileInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;

/**
 * File segments writer
 */
class WriteFile implements WriteFileInterface
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var SegmentNames
     */
    private $segmentNames;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param DriverInterface $driver
     * @param SegmentNames $segmentNames
     * @param Filesystem|null $filesystem
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        DriverInterface $driver,
        SegmentNames $segmentNames,
        Filesystem $filesystem = null
    ) {
        $this->segmentNames = $segmentNames;
        $this->filesystem = $filesystem ?? ObjectManager::getInstance()->get(Filesystem::class);
    }

    /**
     * Write file object to the filesystem
     *
     * @param FileInterface $file
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function execute(FileInterface $file): void
    {
        $resource = $this->getDriver()->fileOpen($file->getPath(), 'wb');

        $this->writeSegments($resource, $file->getSegments());
        $this->getDriver()->fileClose($resource);
    }

    /**
     * Write gif segment
     *
     * @param resource $resource
     * @param SegmentInterface[] $segments
     */
    private function writeSegments($resource, array $segments): void
    {
        foreach ($segments as $segment) {
            $this->getDriver()->fileWrite(
                $resource,
                $segment->getData()
            );
        }
        $this->getDriver()->fileWrite($resource, pack("C", ord(";")));
    }

    /**
     * Returns current driver for media directory
     *
     * @return DriverInterface
     * @throws FileSystemException
     */
    private function getDriver(): DriverInterface
    {
        if ($this->driver === null) {
            $this->driver = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getDriver();
        }

        return $this->driver;
    }
}
