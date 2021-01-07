<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Png\Segment;

use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\ReadMetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Jpeg EXIF Reader
 */
class ReadExif implements ReadMetadataInterface
{
    private const EXIF_SEGMENT_NAME = 'eXIf';

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
        if (!is_callable('exif_read_data')) {
            throw new LocalizedException(
                __('exif_read_data() must be enabled in php configuration')
            );
        }

        foreach ($file->getSegments() as $segment) {
            if ($this->isExifSegment($segment)) {
                return $this->getExifData($segment);
            }
        }

        return $this->metadataFactory->create([
            'title' => null,
            'description' => null,
            'keywords' => null
        ]);
    }

    /**
     * Parese exif data from segment
     *
     * @param SegmentInterface $segment
     */
    private function getExifData(SegmentInterface $segment): MetadataInterface
    {
        $title = null;
        $description = null;
        $keywords = [];

        $data = exif_read_data('data://image/jpeg;base64,' . base64_encode($segment->getData()));

        if ($data) {
            $title = isset($data['DocumentName']) ? $data['DocumentName'] : null;
            $description = isset($data['ImageDescription']) ? $data['ImageDescription'] : null;
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
        return strcmp($segment->getName(), self::EXIF_SEGMENT_NAME) === 0;
    }
}
