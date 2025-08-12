<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Png;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\ReadFileInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterfaceFactory;
use Magento\Framework\Exception\ValidatorException;

/**
 * File segments reader
 */
class ReadFile implements ReadFileInterface
{
    private const PNG_FILE_START = "\x89PNG\x0d\x0a\x1a\x0a";
    private const PNG_MARKER_IMAGE_END = 'IEND';

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
     * @param DriverInterface $driver
     * @param FileInterfaceFactory $fileFactory
     * @param SegmentInterfaceFactory $segmentFactory
     * @param Filesystem $filesystem
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        DriverInterface $driver,
        FileInterfaceFactory $fileFactory,
        SegmentInterfaceFactory $segmentFactory,
        ?Filesystem $filesystem = null
    ) {
        $this->fileFactory = $fileFactory;
        $this->segmentFactory = $segmentFactory;
        $this->filesystem = $filesystem ?? ObjectManager::getInstance()->get(Filesystem::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(string $path): FileInterface
    {
        $resource = $this->getDriver()->fileOpen($path, 'rb');
        $header = $this->readHeader($resource);

        if ($header != self::PNG_FILE_START) {
            $this->getDriver()->fileClose($resource);
            throw new ValidatorException(__('Not a PNG image'));
        }

        do {
            $header = $this->readHeader($resource);
            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            $segmentHeader = unpack('Nsize/a4type', $header);
            $data = $this->read($resource, $segmentHeader['size']);
            $segments[] = $this->segmentFactory->create([
                'name' => $segmentHeader['type'],
                'data' => $data
            ]);
            $cyclicRedundancyCheck = $this->read($resource, 4);

            if (pack('N', crc32($segmentHeader['type'] . $data)) != $cyclicRedundancyCheck) {
                throw new LocalizedException(__('The image is corrupted'));
            }
        } while ($header
            && $segmentHeader['type'] != self::PNG_MARKER_IMAGE_END
            && !$this->getDriver()->endOfFile($resource)
        );

        $this->getDriver()->fileClose($resource);

        return $this->fileFactory->create([
            'path' => $path,
            'segments' => $segments
        ]);
    }

    /**
     * Read 8 bytes
     *
     * @param resource $resource
     * @return string
     * @throws FileSystemException
     */
    private function readHeader($resource): string
    {
        return $this->read($resource, 8);
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
