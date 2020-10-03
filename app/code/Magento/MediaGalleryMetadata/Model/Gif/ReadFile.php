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
use Magento\MediaGalleryMetadataApi\Model\FileInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\ReadFileInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterfaceFactory;
use Magento\Framework\Exception\ValidatorException;

/**
 * File segments reader
 */
class ReadFile implements ReadFileInterface
{
    /**
     * @var DriverInterface
     */
    private $driver;

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
     */
    public function __construct(
        DriverInterface $driver,
        FileInterfaceFactory $fileFactory,
        SegmentInterfaceFactory $segmentFactory,
        SegmentNames $segmentNames
    ) {
        $this->driver = $driver;
        $this->fileFactory = $fileFactory;
        $this->segmentFactory = $segmentFactory;
        $this->segmentNames = $segmentNames;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $path): FileInterface
    {
        $resource = $this->driver->fileOpen($path, 'rb');

        $header = $this->read($resource, 3);

        if ($header != "GIF") {
            $this->driver->fileClose($resource);
            throw new ValidatorException(__('Not a GIF image'));
        }

        $version = $this->read($resource, 3);

        if (!in_array($version, ['87a', '89a'])) {
            $this->driver->fileClose($resource);
            throw new LocalizedException(__('Unexpected GIF version'));
        }

        $headerSegment = $this->segmentFactory->create([
            'name' => 'header',
            'data' => $header . $version
        ]);

        $width = $this->read($resource, 2);
        $height = $this->read($resource, 2);
        $bitPerPixelBinary = $this->read($resource, 1);
        $bitPerPixel = $this->getBitPerPixel($bitPerPixelBinary);
        $backgroundAndAspectRatio = $this->read($resource, 2);
        $globalColorTable = $this->getGlobalColorTable($resource, $bitPerPixel);

        $generalSegment = $this->segmentFactory->create([
            'name' => 'header2',
            'data' => $width . $height . $bitPerPixelBinary . $backgroundAndAspectRatio . $globalColorTable
        ]);

        $segments = $this->getSegments($resource);

        array_unshift($segments, $headerSegment, $generalSegment);

        return $this->fileFactory->create([
            'path' => $path,
            'segments' => $segments
        ]);
    }

    /**
     * Read gif segments
     *
     * @param resource $resource
     * @return SegmentInterface[]
     * @throws FileSystemException
     */
    private function getSegments($resource): array
    {
        $gifFrameSeparator = pack("C", ord(","));
        $gifExtensionSeparator = pack("C", ord("!"));
        $gifTerminator = pack("C", ord(";"));

        $segments = [];
        do {
            $separator = $this->read($resource, 1);

            if ($separator == $gifTerminator) {
                return $segments;
            }

            if ($separator == $gifFrameSeparator) {
                $segments[] = $this->segmentFactory->create([
                    'name' => 'frame',
                    'data' => $gifFrameSeparator . $this->readFrame($resource)
                ]);
                continue;
            }

            if ($separator != $gifExtensionSeparator) {
                throw new LocalizedException(__('The file is corrupted'));
            }

            $segments[] = $this->getExtensionSegment($resource);
        } while (!$this->driver->endOfFile($resource));

        return $segments;
    }

    /**
     * Read extension segment
     *
     * @param resource $resource
     * @return SegmentInterface
     * @throws FileSystemException
     */
    private function getExtensionSegment($resource): SegmentInterface
    {
        $gifExtensionSeparator = pack("C", ord("!"));
        $extensionCodeBinary = $this->read($resource, 1);
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $extensionCode = unpack('C', $extensionCodeBinary)[1];

        if ($extensionCode == 0xF9) {
            return $this->segmentFactory->create([
                'name' => 'Graphics Control Extension',
                'data' => $gifExtensionSeparator . $extensionCodeBinary . $this->readBlock($resource)
            ]);
        }

        if ($extensionCode == 0xFE) {
            return $this->segmentFactory->create([
                'name' => 'comment',
                'data' => $gifExtensionSeparator . $extensionCodeBinary . $this->readBlock($resource)
            ]);
        }

        if ($extensionCode != 0xFF) {
            return $this->segmentFactory->create([
                'name' => 'Programm extension',
                'data' => $gifExtensionSeparator . $extensionCodeBinary . $this->readBlock($resource)
            ]);
        }

        $blockLengthBinary = $this->read($resource, 1);
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $blockLength = unpack('C', $blockLengthBinary)[1];
        $name = $this->read($resource, $blockLength);

        if ($blockLength != 11) {
            throw new LocalizedException(__('The file is corrupted'));
        }

        if ($name == 'XMP DataXMP') {
            return $this->segmentFactory->create([
                'name' => $name,
                'data' =>  $gifExtensionSeparator . $extensionCodeBinary . $blockLengthBinary
                    . $name . $this->readBlockWithSubblocks($resource)
            ]);
        }

        return $this->segmentFactory->create([
            'name' => $name,
            'data' => $gifExtensionSeparator . $extensionCodeBinary . $blockLengthBinary
            . $name . $this->readBlock($resource)
        ]);
    }

    /**
     * Read gif frame
     *
     * @param resource $resource
     * @return string
     * @throws FileSystemException
     */
    private function readFrame($resource): string
    {
        $boundingBox = $this->read($resource, 8);
        $bitPerPixelBinary = $this->read($resource, 1);
        $bitPerPixel = $this->getBitPerPixel($bitPerPixelBinary);
        $globalColorTable = $this->getGlobalColorTable($resource, $bitPerPixel);
        return $boundingBox . $bitPerPixelBinary . $globalColorTable . $this->read($resource, 1)
            . $this->readBlockWithSubblocks($resource);
    }

    /**
     * Retrieve bits per pixel value
     *
     * @param string $data
     * @return int
     */
    private function getBitPerPixel(string $data): int
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $bitPerPixel = unpack('C', $data)[1];
        $bpp = ($bitPerPixel & 7) + 1;
        $bitPerPixel >>= 7;
        $haveMap = $bitPerPixel & 1;
        return $haveMap ? $bpp : 0;
    }

    /**
     * Read global color table
     *
     * @param resource $resource
     * @param int $bitPerPixel
     * @return string
     * @throws FileSystemException
     */
    private function getGlobalColorTable($resource, int $bitPerPixel): string
    {
        $globalColorTable = '';
        if ($bitPerPixel > 0) {
            $max = pow(2, $bitPerPixel);
            for ($i = 1; $i <= $max; ++$i) {
                $globalColorTable .= $this->read($resource, 3);
            }
        }
        return $globalColorTable;
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

        while (!$this->driver->endOfFile($resource) && strlen($data) < $length) {
            $data .= $this->driver->fileRead($resource, $length - strlen($data));
        }

        return $data;
    }

    /**
     * Read the block stored in multiple sections
     *
     * @param resource $resource
     * @return string
     * @throws FileSystemException
     */
    private function readBlockWithSubblocks($resource): string
    {
        $data = '';
        $subLength = $this->read($resource, 1);

        while ($subLength !== "\0") {
            $data .= $subLength . $this->read($resource, ord($subLength));
            $subLength = $this->read($resource, 1);
        }

        return $data . $subLength;
    }

    /**
     * Read gif block
     *
     * @param resource $resource
     * @return string
     * @throws FileSystemException]
     */
    private function readBlock($resource): string
    {
        $blockLengthBinary = $this->read($resource, 1);
        $blockLength = ord($blockLengthBinary);
        if ($blockLength == 0) {
            return '';
        }
        return $blockLengthBinary . $this->read($resource, $blockLength) . $this->read($resource, 1);
    }
}
