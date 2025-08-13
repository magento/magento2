<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Jpeg;

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
 * File segments reader
 */
class WriteFile implements WriteFileInterface
{
    private const MARKER_IMAGE_FILE_START = "\xD8";
    private const MARKER_IMAGE_PREFIX = "\xFF";
    private const MARKER_IMAGE_END = "\xD9";

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
     * @param Filesystem $filesystem
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        DriverInterface $driver,
        SegmentNames $segmentNames,
        ?Filesystem $filesystem = null
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
        foreach ($file->getSegments() as $segment) {
            if ($segment->getName() != 'CompressedImage' && strlen($segment->getData()) > 0xfffd) {
                throw new LocalizedException(__('A Header is too large to fit in the segment!'));
            }
        }

        $resource = $this->getDriver()->fileOpen($file->getPath(), 'wb');

        $this->getDriver()->fileWrite($resource, self::MARKER_IMAGE_PREFIX . self::MARKER_IMAGE_FILE_START);
        $this->writeSegments($resource, $file->getSegments());
        $this->getDriver()->fileWrite($resource, self::MARKER_IMAGE_PREFIX . self::MARKER_IMAGE_END);
        $this->getDriver()->fileClose($resource);
    }

    /**
     * Write jpeg segment
     *
     * @param resource $resource
     * @param SegmentInterface[] $segments
     */
    private function writeSegments($resource, array $segments): void
    {
        foreach ($segments as $segment) {
            if ($segment->getName() !== 'CompressedImage') {
                $this->getDriver()->fileWrite(
                    $resource,
                    //phpcs:ignore Magento2.Functions.DiscouragedFunction
                    self::MARKER_IMAGE_PREFIX . chr($this->segmentNames->getSegmentType($segment->getName()))
                );
                $this->getDriver()->fileWrite($resource, pack("n", strlen($segment->getData()) + 2));
            }
            $this->getDriver()->fileWrite($resource, $segment->getData());
        }
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
