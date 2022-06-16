<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Jpeg\Segment;

use Magento\MediaGalleryMetadata\Model\AddXmpMetadata;
use Magento\MediaGalleryMetadata\Model\XmpTemplate;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\WriteMetadataInterface;

/**
 * Jpeg XMP Writer
 */
class WriteXmp implements WriteMetadataInterface
{
    private const XMP_SEGMENT_NAME = 'APP1';
    private const XMP_SEGMENT_START = "http://ns.adobe.com/xap/1.0/\x00";
    private const XMP_DATA_START_POSITION = 29;

    /**
     * @var SegmentInterfaceFactory
     */
    private $segmentFactory;

    /**
     * @var FileInterfaceFactory
     */
    private $fileFactory;

    /**
     * @var AddXmpMetadata
     */
    private $addXmpMetadata;

    /**
     * @var XmpTemplate
     */
    private $xmpTemplate;

    /**
     * @param FileInterfaceFactory $fileFactory
     * @param SegmentInterfaceFactory $segmentFactory
     * @param AddXmpMetadata $addXmpMetadata
     * @param XmpTemplate $xmpTemplate
     */
    public function __construct(
        FileInterfaceFactory $fileFactory,
        SegmentInterfaceFactory $segmentFactory,
        AddXmpMetadata $addXmpMetadata,
        XmpTemplate $xmpTemplate
    ) {
        $this->fileFactory = $fileFactory;
        $this->segmentFactory = $segmentFactory;
        $this->addXmpMetadata = $addXmpMetadata;
        $this->xmpTemplate = $xmpTemplate;
    }

    /**
     * Add metadata to the file
     *
     * @param FileInterface $file
     * @param MetadataInterface $metadata
     * @return FileInterface
     */
    public function execute(FileInterface $file, MetadataInterface $metadata): FileInterface
    {
        $segments = $file->getSegments();
        $xmpSegments = [];
        foreach ($segments as $key => $segment) {
            if ($this->isSegmentXmp($segment)) {
                $xmpSegments[$key] = $segment;
            }
        }

        if (empty($xmpSegments)) {
            return $this->fileFactory->create([
                'path' => $file->getPath(),
                'segments' => $this->insertXmpSegment($segments, $this->createXmpSegment($metadata))
            ]);
        }

        foreach ($xmpSegments as $key => $segment) {
            $segments[$key] = $this->updateSegment($segment, $metadata);
        }

        return $this->fileFactory->create([
            'path' => $file->getPath(),
            'segments' => $segments
        ]);
    }

    /**
     * Insert XMP segment to image segments (at position 1)
     *
     * @param SegmentInterface[] $segments
     * @param SegmentInterface $xmpSegment
     * @return SegmentInterface[]
     */
    private function insertXmpSegment(array $segments, SegmentInterface $xmpSegment): array
    {
        return array_merge(array_slice($segments, 0, 2), [$xmpSegment], array_slice($segments, 2));
    }

    /**
     * Write new segment  metadata
     *
     * @param MetadataInterface $metadata
     * @return SegmentInterface
     */
    private function createXmpSegment(MetadataInterface $metadata): SegmentInterface
    {
        $xmpData = $this->xmpTemplate->get();
        return $this->segmentFactory->create([
            'name' => self::XMP_SEGMENT_NAME,
            'data' => self::XMP_SEGMENT_START . $this->addXmpMetadata->execute($xmpData, $metadata)
        ]);
    }

    /**
     * Add metadata to the segment
     *
     * @param SegmentInterface $segment
     * @param MetadataInterface $metadata
     * @return SegmentInterface
     */
    private function updateSegment(SegmentInterface $segment, MetadataInterface $metadata): SegmentInterface
    {
        $data = $segment->getData();
        $start = substr($data, 0, self::XMP_DATA_START_POSITION);
        $xmpData = substr($data, self::XMP_DATA_START_POSITION);
        return $this->segmentFactory->create([
            'name' => $segment->getName(),
            'data' => $start . $this->addXmpMetadata->execute($xmpData, $metadata)
        ]);
    }

    /**
     * Check if segment contains XMP data
     *
     * @param SegmentInterface $segment
     * @return bool
     */
    private function isSegmentXmp(SegmentInterface $segment): bool
    {
        return $segment->getName() === self::XMP_SEGMENT_NAME
            && strncmp($segment->getData(), self::XMP_SEGMENT_START, self::XMP_DATA_START_POSITION) == 0;
    }
}
