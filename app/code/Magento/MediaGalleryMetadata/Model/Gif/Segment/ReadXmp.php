<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Gif\Segment;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryMetadata\Model\GetXmpMetadata;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\ReadMetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;

/**
 * XMP Reader for gif file format
 */
class ReadXmp implements ReadMetadataInterface
{
    private const XMP_SEGMENT_NAME = 'XMP DataXMP';
    /**
     * see XMP Specification Part 3, 1.1.2 GIF
     */
    private const MAGIC_TRAILER_LENGTH = 258;
    private const MAGIC_TRAILER_START = "\x01\xFF\xFE";
    private const MAGIC_TRAILER_END = "\x03\x02\x01\x00\x00";

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
            if ($this->isXmp($segment)) {
                return $this->getXmpMetadata->execute($this->getXmpData($segment));
            }
        }
        return $this->metadataFactory->create([
            'title' => '',
            'description' => '',
            'keywords' => []
        ]);
    }

    /**
     * Does segment contain XMP data
     *
     * @param SegmentInterface $segment
     * @return bool
     */
    private function isXmp(SegmentInterface $segment): bool
    {
        return $segment->getName() === self::XMP_SEGMENT_NAME;
    }

    /**
     * Get XMP xml
     *
     * @param SegmentInterface $segment
     * @return string
     */
    private function getXmpData(SegmentInterface $segment): string
    {
        $xmp = substr($segment->getData(), 14);

        if (substr($xmp, -self::MAGIC_TRAILER_LENGTH, 3) !== self::MAGIC_TRAILER_START
            || substr($xmp, -5) !== self::MAGIC_TRAILER_END
        ) {
            throw new LocalizedException(__('XMP data is corrupted'));
        }

        return substr($xmp, 0, -self::MAGIC_TRAILER_LENGTH);
    }
}
