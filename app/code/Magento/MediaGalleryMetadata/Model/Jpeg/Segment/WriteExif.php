<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Jpeg\Segment;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryMetadata\Model\AddExifMetadata;
use Magento\MediaGalleryMetadata\Model\Jpeg\ReadFile;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\WriteMetadataInterface;

/**
 * Responsible to write and exif data to the JPEG file.
 */
class WriteExif implements WriteMetadataInterface
{
    private const EXIF_SEGMENT_NAME = 'eXIf';

    private const EXIF_SEGMENT_START = "Exif\x00";

    private const EXIF_DATA_START_POSITION = 0;

    /**
     * @var SegmentInterfaceFactory
     */
    private $segmentFactory;

    /**
     * @var FileInterfaceFactory
     */
    private $fileFactory;

    /**
     * @var AddExifMetadata
     */
    private $addExifMetadata;

    /**
     * @var ReadFile
     */
    private $fileReader;

    /**
     * @param FileInterfaceFactory $fileFactory
     * @param SegmentInterfaceFactory $segmentFactory
     * @param AddExifMetadata $addExifMetadata
     * @param ReadFile $fileReader
     */
    public function __construct(
        FileInterfaceFactory $fileFactory,
        SegmentInterfaceFactory $segmentFactory,
        AddExifMetadata $addExifMetadata,
        ReadFile $fileReader
    ) {
        $this->fileFactory = $fileFactory;
        $this->segmentFactory = $segmentFactory;
        $this->addExifMetadata = $addExifMetadata;
        $this->fileReader = $fileReader;
    }

    /**
     * @inheritdoc
     */
    public function execute(FileInterface $file, MetadataInterface $metadata): FileInterface
    {
        if (!is_callable('exif_read_data')) {
            throw new LocalizedException(
                __('exif_read_data() must be enabled in php configuration')
            );
        }

        $segments = $file->getSegments();
        $exifSegments = [];
        foreach ($segments as $key => $segment) {
            if ($this->isExifSegment($segment)) {
                $exifSegments [$key] = $segment;
            }
        }

        foreach ($exifSegments  as $segment) {
            return  $this->addExifMetadata->execute($file, $metadata, $segment);
        }
        return  $this->addExifMetadata->execute($file, $metadata, null);
    }

    /**
     * Does segment contain Exif data.
     *
     * @param SegmentInterface $segment
     * @return bool
     */
    private function isExifSegment(SegmentInterface $segment): bool
    {
        return $segment->getName() === self::EXIF_SEGMENT_NAME
            && strncmp(
                substr($segment->getData(), self::EXIF_DATA_START_POSITION, 5),
                self::EXIF_SEGMENT_START,
                5
            ) == 0;
    }
}
