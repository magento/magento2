<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Jpeg\Segment;

use Magento\MediaGalleryMetadata\Model\GetXmpMetadata;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\ReadMetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;

/**
 * Jpeg XMP Reader
 */
class ReadXmp implements ReadMetadataInterface
{
    private const XMP_SEGMENT_NAME = 'APP1';
    private const XMP_SEGMENT_START = "http://ns.adobe.com/xap/1.0/\x00";
    private const XMP_DATA_START_POSITION = 29;

    /**
     * @var MetadataInterfaceFactory
     */
    private $metadataFactory;

    /**
     * @var GetXmpMetadata
     */
    private $getXmpMetadata;

    /**
     * @param MetadataInterfaceFactory $metadataFactory
     * @param GetXmpMetadata $getXmpMetadata
     */
    public function __construct(MetadataInterfaceFactory $metadataFactory, GetXmpMetadata $getXmpMetadata)
    {
        $this->metadataFactory = $metadataFactory;
        $this->getXmpMetadata = $getXmpMetadata;
    }

    /**
     * @inheritdoc
     */
    public function execute(FileInterface $file): MetadataInterface
    {
        foreach ($file->getSegments() as $segment) {
            if ($this->isSegmentXmp($segment)) {
                return $this->getXmpMetadata->execute($this->getXmpData($segment));
            }
        }
        return $this->metadataFactory->create([
            'title' => null,
            'description' => null,
            'keywords' => null
        ]);
    }

    /**
     * Does segment contain XMP data
     *
     * @param SegmentInterface $segment
     * @return bool
     */
    private function isSegmentXmp(SegmentInterface $segment): bool
    {
        return $segment->getName() === self::XMP_SEGMENT_NAME
            && strncmp($segment->getData(), self::XMP_SEGMENT_START, self::XMP_DATA_START_POSITION) == 0;
    }

    /**
     * Get XMP xml
     *
     * @param SegmentInterface $segment
     * @return string
     */
    private function getXmpData(SegmentInterface $segment): string
    {
        return substr($segment->getData(), self::XMP_DATA_START_POSITION);
    }
}
