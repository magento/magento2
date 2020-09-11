<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Jpeg\Segment;

use Magento\MediaGalleryMetadata\Model\GetIptcMetadata;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\ReadMetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;

/**
 * IPTC Reader to read IPTC data for jpeg image
 */
class ReadIptc implements ReadMetadataInterface
{
    private const IPTC_SEGMENT_NAME = 'APP13';
    private const IPTC_SEGMENT_START = 'Photoshop 3.0';
    private const IPTC_DATA_START_POSITION = 0;

    /**
     * @var MetadataInterfaceFactory
     */
    private $metadataFactory;

    /**
     * @var GetIptcMetadata
     */
    private $getIptcData;

    /**
     * @param GetIptcMetadata $getIptcData
     * @param MetadataInterfaceFactory $metadataFactory
     */
    public function __construct(
        GetIptcMetadata $getIptcData,
        MetadataInterfaceFactory $metadataFactory
    ) {
        $this->getIptcData = $getIptcData;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(FileInterface $file): MetadataInterface
    {
        foreach ($file->getSegments() as $segment) {
            if ($this->isIptcSegment($segment)) {
                return $this->getIptcData->execute($segment->getData());
            }
        }
        return $this->metadataFactory->create([
            'title' => null,
            'description' => null,
            'keywords' => null
        ]);
    }

    /**
     * Does segment contain IPTC data
     *
     * @param SegmentInterface $segment
     * @return bool
     */
    private function isIptcSegment(SegmentInterface $segment): bool
    {
        return $segment->getName() === self::IPTC_SEGMENT_NAME
            && strncmp($segment->getData(), self::IPTC_SEGMENT_START, self::IPTC_DATA_START_POSITION) == 0;
    }
}
