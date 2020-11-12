<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Png;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\MediaGalleryMetadata\Model\SegmentNames;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\WriteFileInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;

/**
 * File segments reader
 */
class WriteFile implements WriteFileInterface
{
    private const PNG_FILE_START = "\x89PNG\x0d\x0a\x1a\x0a";

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
     * Write PNG file to filesystem
     *
     * @param FileInterface $file
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function execute(FileInterface $file): void
    {
        $resource = $this->driver->fileOpen($file->getPath(), 'wb');

        $this->driver->fileWrite($resource, self::PNG_FILE_START);
        $this->writeSegments($resource, $file->getSegments());
        $this->driver->fileClose($resource);
    }

    /**
     * Write PNG segments
     *
     * @param resource $resource
     * @param SegmentInterface[] $segments
     */
    private function writeSegments($resource, array $segments): void
    {
        foreach ($segments as $segment) {
            $this->driver->fileWrite($resource, pack("N", strlen($segment->getData())));
            $this->driver->fileWrite($resource, pack("a4", $segment->getName()));
            $this->driver->fileWrite($resource, $segment->getData());
            $this->driver->fileWrite($resource, pack("N", crc32($segment->getName() . $segment->getData())));
        }
    }
}
