<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\MediaGalleryMetadata\Model\Jpeg\ReadFile;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;

/**
 * Write exif data to the file return updated FileInterface with exif data.
 */
class AddExifMetadata
{
    private const EXIF_SEGMENT_NAME = 'eXIf';

    private const EXIF_TITLE_SEGMENT = '0x010d';

    private const EXIF_DESCRIPTION_SEGMENT = '0x010e';

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var ReadFile
     */
    private $fileReader;

    /**
     * @var FileInterfaceFactory
     */
    private $fileFactory;

    /**
     * @param FileInterfaceFactory $fileFactory
     * @param DriverInterface $driver
     * @param ReadFile $fileReader
     */
    public function __construct(
        FileInterfaceFactory $fileFactory,
        DriverInterface $driver,
        ReadFile $fileReader
    ) {
        $this->fileFactory = $fileFactory;
        $this->driver = $driver;
        $this->fileReader = $fileReader;
    }

    /**
     * Write exif metadata.
     *
     * @param FileInterface $file
     * @param MetadataInterface $metadata
     * @param null|SegmentInterface $segment
     * @return FileInterface
     */
    public function execute(FileInterface $file, MetadataInterface $metadata, ?SegmentInterface $segment): FileInterface
    {
        if (!is_callable('exif_read_data')) {
            throw new LocalizedException(
                __('exif_read_data() must be enabled in php configuration')
            );
        }

        $exifData =  $segment ? $segment->getData() : [];

        if ($metadata->getTitle() !== null) {
            $exifData[self::EXIF_TITLE_SEGMENT][0] = $metadata->getTitle();
        }

        if ($metadata->getDescription() !== null) {
            $exifData[self::EXIF_DESCRIPTION_SEGMENT][0] = $metadata->getDescription();
        }

        $newExifData = '';
        foreach ($exifData as $tag => $values) {
            foreach ($values as $value) {
                $newExifData .= $this->exifMaketag(2, (int) substr($tag, 2), $value);
            }
        }

        $this->writeFile($file->getPath(), $newExifData);

        $fileWithExif = $this->fileReader->execute($file->getPath());

        return $this->fileFactory->create([
                'path' => $fileWithExif->getPath(),
                'segments' => $this->getSegmentsWithExif($fileWithExif, $file)
        ]);
    }

    /**
     * Return exif segments from file.
     *
     * @param FileInterface $fileWithExif
     * @param FileInterface $originFile
     * @return array
     */
    private function getSegmentsWithExif(FileInterface $fileWithExif, FileInterface $originFile): array
    {
        $segments = $fileWithExif->getSegments();
        $originFileSegments =  $originFile->getSegments();

        foreach ($segments as $key => $segment) {
            if ($segment->getName() === self::EXIF_SEGMENT_NAME) {
                foreach ($originFileSegments as $originKey => $originSegment) {
                    if ($originSegment->getName() === self::EXIF_SEGMENT_NAME) {
                        $originFileSegments[$originKey] = $segments[$key];
                    }
                }
                return $originFileSegments;
            }
        }
        return $originFileSegments;
    }

    /**
     * Write the exif data to the image file directly.
     *
     * @param string $filePath
     * @param string $content
     */
    private function writeFile(string $filePath, string $content): void
    {
        $resource = $this->driver->fileOpen($filePath, 'wb');

        $this->driver->fileWrite($resource, $content);
        $this->driver->fileClose($resource);
    }

    /**
     * Create an exif tag.
     *
     * @param int $rec
     * @param int $tag
     * @param string $value
     * @return string
     */
    private function exifMaketag(int $rec, int $tag, string $value): string
    {
        //phpcs:disable Magento2.Functions.DiscouragedFunction
        $length = strlen($value);
        $retval = chr(0x1C) . chr($rec) . chr($tag);

        if ($length < 0x8000) {
            $retval .= chr($length >> 8) . chr($length & 0xFF);
        } else {
            $retval .= chr(0x80) .
                   chr(0x04) .
                   chr(($length >> 24) & 0xFF) .
                   chr(($length >> 16) & 0xFF) .
                   chr(($length >> 8) & 0xFF) .
                   chr($length & 0xFF);
        }
        //phpcs:enable Magento2.Functions.DiscouragedFunction
        return $retval . $value;
    }
}
