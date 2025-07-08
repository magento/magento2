<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Jpeg;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\MediaGalleryMetadata\Model\SegmentNames;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\ReadFileInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterfaceFactory;

/**
 * Jpeg file reader
 */
class ReadFile implements ReadFileInterface
{
    private const MARKER_IMAGE_FILE_START = "\xD8";
    private const MARKER_PREFIX = "\xFF";
    private const MARKER_IMAGE_END = "\xD9";
    private const MARKER_IMAGE_START = "\xDA";

    private const TWO_BYTES = 2;
    private const ONE_MEGABYTE = 1048576;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var SegmentInterfaceFactory
     */
    private $segmentFactory;

    /**
     * @var FileInterfaceFactory
     */
    private $fileFactory;

    /**
     * @var SegmentNames
     */
    private $segmentNames;

    /**
     * @param DriverInterface $driver
     * @param FileInterfaceFactory $fileFactory
     * @param SegmentInterfaceFactory $segmentFactory
     * @param SegmentNames $segmentNames
     * @param Filesystem|null $filesystem
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        DriverInterface $driver,
        FileInterfaceFactory $fileFactory,
        SegmentInterfaceFactory $segmentFactory,
        SegmentNames $segmentNames,
        ?Filesystem $filesystem = null
    ) {
        $this->fileFactory = $fileFactory;
        $this->segmentFactory = $segmentFactory;
        $this->segmentNames = $segmentNames;
        $this->filesystem = $filesystem ?? ObjectManager::getInstance()->get(Filesystem::class);
    }

    /**
     * Is reader applicable
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    private function isApplicable(string $path): bool
    {
        $resource = $this->getDriver()->fileOpen($path, 'rb');
        try {
            $marker = $this->readMarker($resource);
        } catch (LocalizedException $exception) {
            return false;
        }
        $this->getDriver()->fileClose($resource);

        return $marker == self::MARKER_IMAGE_FILE_START;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $path): FileInterface
    {
        if (!$this->isApplicable($path)) {
            throw new ValidatorException(__('Not a JPEG image'));
        }

        $resource = $this->getDriver()->fileOpen($path, 'rb');
        $marker = $this->readMarker($resource);

        if ($marker != self::MARKER_IMAGE_FILE_START) {
            $this->getDriver()->fileClose($resource);
            throw new ValidatorException(__('Not a JPEG image'));
        }

        do {
            $marker = $this->readMarker($resource);
            $segments[] = $this->readSegment($resource, ord($marker));
        } while (($marker != self::MARKER_IMAGE_START) && (!$this->getDriver()->endOfFile($resource)));

        if ($marker != self::MARKER_IMAGE_START) {
            throw new LocalizedException(__('File is corrupted'));
        }

        $segments[] = $this->segmentFactory->create([
            'name' => 'CompressedImage',
            'data' => $this->readCompressedImage($resource)
        ]);

        $this->getDriver()->fileClose($resource);

        return $this->fileFactory->create([
            'path' => $path,
            'segments' => $segments
        ]);
    }

    /**
     * Read jpeg marker
     *
     * @param resource $resource
     * @return string
     * @throws FileSystemException
     */
    private function readMarker($resource): string
    {
        $data = $this->read($resource, self::TWO_BYTES);

        if ($data[0] != self::MARKER_PREFIX) {
            $this->getDriver()->fileClose($resource);
            throw new LocalizedException(__('File is corrupted'));
        }

        return $data[1];
    }

    /**
     * Read compressed image
     *
     * @param resource $resource
     * @return string
     * @throws FileSystemException
     */
    private function readCompressedImage($resource): string
    {
        $compressedImage = '';
        do {
            $compressedImage .= $this->read($resource, self::ONE_MEGABYTE);
        } while (!$this->getDriver()->endOfFile($resource));

        $endOfImageMarkerPosition = strpos($compressedImage, self::MARKER_PREFIX . self::MARKER_IMAGE_END);

        if ($endOfImageMarkerPosition !== false) {
            $compressedImage = substr($compressedImage, 0, $endOfImageMarkerPosition);
        }

        return $compressedImage;
    }

    /**
     * Read jpeg segment
     *
     * @param resource $resource
     * @param int $segmentType
     * @return SegmentInterface
     * @throws FileSystemException
     */
    private function readSegment($resource, int $segmentType): SegmentInterface
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $segmentSize = unpack('nsize', $this->read($resource, 2))['size'] - 2;
        return $this->segmentFactory->create([
            'name' => $this->segmentNames->getSegmentName($segmentType),
            'data' => $this->read($resource, $segmentSize)
        ]);
    }

    /**
     * Read wrapper
     *
     * @param resource $resource
     * @param int $length
     * @return string
     * @throws FileSystemException
     */
    private function read($resource, int $length): string
    {
        $data = '';

        while (!$this->getDriver()->endOfFile($resource) && strlen($data) < $length) {
            $data .= $this->getDriver()->fileRead($resource, $length - strlen($data));
        }

        return $data;
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
