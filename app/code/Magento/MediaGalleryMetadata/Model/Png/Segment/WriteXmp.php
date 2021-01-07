<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Png\Segment;

use Magento\MediaGalleryMetadata\Model\AddXmpMetadata;
use Magento\MediaGalleryMetadata\Model\XmpTemplate;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\WriteMetadataInterface;

/**
 * XMP Writer for png format
 */
class WriteXmp implements WriteMetadataInterface
{
    private const XMP_SEGMENT_NAME = 'iTXt';
    private const XMP_SEGMENT_START = "XML:com.adobe.xmp\x00";

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
     * Add xmp metadata to the png file
     *
     * @param FileInterface $file
     * @param MetadataInterface $metadata
     * @return FileInterface
     */
    public function execute(FileInterface $file, MetadataInterface $metadata): FileInterface
    {
        $segments = $file->getSegments();
        $pngXmpSegments = [];
        foreach ($segments as $key => $segment) {
            if ($this->isXmpSegment($segment)) {
                $pngXmpSegments[$key] = $segment;
            }
        }

        if (empty($pngXmpSegments)) {
            return $this->fileFactory->create([
                'path' => $file->getPath(),
                'segments' => $this->insertPngXmpSegment($segments, $this->createPngXmpSegment($metadata))
            ]);
        }

        foreach ($pngXmpSegments as $key => $segment) {
            $segments[$key] = $this->updateSegment($segment, $metadata);
        }

        return $this->fileFactory->create([
            'path' => $file->getPath(),
            'segments' => $segments
        ]);
    }

    /**
     * Insert XMP segment to image png segments before IEND chunk
     *
     * @param SegmentInterface[] $segments
     * @param SegmentInterface $xmpSegment
     * @return SegmentInterface[]
     */
    private function insertPngXmpSegment(array $segments, SegmentInterface $xmpSegment): array
    {
        $iendSegmentIndex = count($segments) - 1;
        return array_merge(
            array_slice($segments, 0, $iendSegmentIndex),
            [$xmpSegment],
            array_slice($segments, $iendSegmentIndex)
        );
    }

    /**
     * Write new png segment  metadata
     *
     * @param MetadataInterface $metadata
     * @return SegmentInterface
     */
    private function createPngXmpSegment(MetadataInterface $metadata): SegmentInterface
    {
        $xmpData = $this->xmpTemplate->get();
        return $this->segmentFactory->create([
            'name' => self::XMP_SEGMENT_NAME,
            'data' => self::XMP_SEGMENT_START . $this->addXmpMetadata->execute($xmpData, $metadata)
        ]);
    }

    /**
     * Add metadata to the png xmp segment
     *
     * @param SegmentInterface $segment
     * @param MetadataInterface $metadata
     * @return SegmentInterface
     */
    private function updateSegment(SegmentInterface $segment, MetadataInterface $metadata): SegmentInterface
    {
        return $this->segmentFactory->create([
            'name' => $segment->getName(),
            'data' => self::XMP_SEGMENT_START . $this->addXmpMetadata->execute($this->getXmpData($segment), $metadata)
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
