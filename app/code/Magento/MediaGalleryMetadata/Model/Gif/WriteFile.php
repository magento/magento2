<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Gif;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
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
     * @param DriverInterface $driver
     * @param SegmentNames $segmentNames
     */
    public function __construct(
        DriverInterface $driver,
        SegmentNames $segmentNames
    ) {
        $this->driver = $driver;
        $this->segmentNames = $segmentNames;
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
        $resource = $this->driver->fileOpen($file->getPath(), 'wb');

        $this->writeSegments($resource, $file->getSegments());
        $this->driver->fileClose($resource);
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
            $this->driver->fileWrite(
                $resource,
                $segment->getData()
            );
        }
        $this->driver->fileWrite($resource, pack("C", ord(";")));
    }
}
