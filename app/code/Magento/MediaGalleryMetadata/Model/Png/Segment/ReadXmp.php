<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Png\Segment;

use Magento\MediaGalleryMetadata\Model\GetXmpMetadata;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\ReadMetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;

/**
 * PNG XMP Reader
 */
class ReadXmp implements ReadMetadataInterface
{
    private const XMP_SEGMENT_NAME = 'iTXt';

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
     * Read metadata from the file
     *
     * @param FileInterface $file
     * @return MetadataInterface
     */
    public function execute(FileInterface $file): MetadataInterface
    {
        foreach ($file->getSegments() as $segment) {
            if ($this->isXmpSegment($segment)) {
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
    private function isXmpSegment(SegmentInterface $segment): bool
    {
        return $segment->getName() === self::XMP_SEGMENT_NAME
            && strpos($segment->getData(), '<x:xmpmeta') !== -1;
    }

    /**
     * Get XMP xml
     *
     * @param SegmentInterface $segment
     * @return string
     */
    private function getXmpData(SegmentInterface $segment): string
    {
        return substr($segment->getData(), strpos($segment->getData(), '<x:xmpmeta'));
    }
}
