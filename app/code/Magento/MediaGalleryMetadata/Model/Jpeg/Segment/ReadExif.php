<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Jpeg\Segment;

use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\ReadMetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;

/**
 * Jpeg EXIF Reader
 */
class ReadExif implements ReadMetadataInterface
{
    private const EXIF_SEGMENT_NAME = 'APP1';
    private const EXIF_SEGMENT_START = "Exif\x00";
    private const EXIF_DATA_START_POSITION = 0;

    /**
     * @var MetadataInterfaceFactory
     */
    private $metadataFactory;

    /**
     * @param MetadataInterfaceFactory $metadataFactory
     */
    public function __construct(
        MetadataInterfaceFactory $metadataFactory
    ) {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(FileInterface $file): MetadataInterface
    {
        $title = null;
        $description = null;
        $keywords = [];

        foreach ($file->getSegments() as $segment) {
            if ($this->isExifSegment($segment)) {
            }
        }
        return $this->metadataFactory->create([
            'title' => $title,
            'description' => $description,
            'keywords' => !empty($keywords) ? $keywords : null
        ]);
    }

    /**
     * Does segment contain Exif data
     *
     * @param SegmentInterface $segment
     * @return bool
     */
    private function isExifSegment(SegmentInterface $segment): bool
    {
        return $segment->getName() === self::EXIF_SEGMENT_NAME
            && strncmp(
                substr($segment->getData(), self::EXIF_DATA_START_POSITION, 4),
                self::EXIF_SEGMENT_START,
                self::EXIF_DATA_START_POSITION
            ) == 0;
    }
}
