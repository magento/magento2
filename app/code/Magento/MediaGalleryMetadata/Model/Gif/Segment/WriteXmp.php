<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Gif\Segment;

use Magento\MediaGalleryMetadata\Model\AddXmpMetadata;
use Magento\MediaGalleryMetadata\Model\XmpTemplate;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\WriteMetadataInterface;

/**
 *  XMP Writer for GIF format
 */
class WriteXmp implements WriteMetadataInterface
{
    private const XMP_SEGMENT_NAME = 'XMP DataXMP';
    private const XMP_DATA_START_POSITION = 14;
    private const MAGIC_TRAILER_START = "\x01\xFF\xFE";
    private const MAGIC_TRAILER_END = "\x03\x02\x01\x00\x00";

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
        $gifSegments = $file->getSegments();
        $xmpGifSegments = [];
        foreach ($gifSegments as $key => $segment) {
            if ($this->isSegmentXmp($segment)) {
                $xmpGifSegments[$key] = $segment;
            }
        }

        if (empty($xmpGifSegments)) {
            return $this->fileFactory->create([
                'path' => $file->getPath(),
                'segments' => $this->insertXmpGifSegment($gifSegments, $this->createXmpSegment($metadata))
            ]);
        }

        foreach ($xmpGifSegments as $key => $segment) {
            $gifSegments[$key] = $this->updateSegment($segment, $metadata);
        }

        return $this->fileFactory->create([
            'path' => $file->getPath(),
            'segments' => $gifSegments
        ]);
    }

    /**
     * Insert XMP segment to gif image segments (at position 3)
     *
     * @param SegmentInterface[] $segments
     * @param SegmentInterface $xmpSegment
     * @return SegmentInterface[]
     */
    private function insertXmpGifSegment(array $segments, SegmentInterface $xmpSegment): array
    {
        return array_merge(array_slice($segments, 0, 4), [$xmpSegment], array_slice($segments, 4));
    }

    /**
     * Return XMP template from string
     *
     * @param string $string
     * @param string $start
     * @param string $end
     */
    private function getXmpData(string $string, string $start, string $end): string
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    /**
     * Write new segment  metadata
     *
     * @param MetadataInterface $metadata
     * @return SegmentInterface
     */
    public function createXmpSegment(MetadataInterface $metadata): SegmentInterface
    {
        $xmpData = $this->xmpTemplate->get();

        $xmpSegment = pack("C", ord("!")) . pack("C", 255) . pack("C", 11) .
                    self::XMP_SEGMENT_NAME . $this->addXmpMetadata->execute($xmpData, $metadata) . "\x01";

        /**
         * Write Magic trailer 258 bytes see XMP Specification Part 3, 1.1.2 GIF
         */
        $i = 255;
        while ($i > 0) {
            $xmpSegment .= pack("C", $i);
            $i--;
        }

        return $this->segmentFactory->create([
            'name' => self::XMP_SEGMENT_NAME,
            'data' => $xmpSegment . "\0\0"
        ]);
    }

    /**
     * Add metadata to the segment
     *
     * @param SegmentInterface $segment
     * @param MetadataInterface $metadata
     * @return SegmentInterface
     */
    public function updateSegment(SegmentInterface $segment, MetadataInterface $metadata): SegmentInterface
    {
        $data = $segment->getData();
        $start = substr($data, 0, self::XMP_DATA_START_POSITION);
        $xmpData = $this->getXmpData($data, self::XMP_SEGMENT_NAME, "\x01");
        $end = substr($data, strpos($data, "\x01"));

        return $this->segmentFactory->create([
            'name' => $segment->getName(),
            'data' => $start . $this->addXmpMetadata->execute($xmpData, $metadata) . $end
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
        return $segment->getName() === self::XMP_SEGMENT_NAME;
    }
}
