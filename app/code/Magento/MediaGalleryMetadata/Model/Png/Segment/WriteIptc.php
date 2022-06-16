<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Png\Segment;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\WriteMetadataInterface;

/**
 * IPTC Writer to write IPTC data for png image
 */
class WriteIptc implements WriteMetadataInterface
{
    private const IPTC_SEGMENT_NAME = 'zTXt';
    private const IPTC_SEGMENT_START = 'iptc';
    private const IPTC_DATA_START_POSITION = 17;
    private const IPTC_SEGMENT_START_STRING = 'Raw profile type iptc';

    /**
     * @var SegmentInterfaceFactory
     */
    private $segmentFactory;

    /**
     * @var FileInterfaceFactory
     */
    private $fileFactory;

    /**
     * @param FileInterfaceFactory $fileFactory
     * @param SegmentInterfaceFactory $segmentFactory
     */
    public function __construct(
        FileInterfaceFactory $fileFactory,
        SegmentInterfaceFactory $segmentFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->segmentFactory = $segmentFactory;
    }

    /**
     * Write iptc metadata to zTXt segment
     *
     * @param FileInterface $file
     * @param MetadataInterface $metadata
     * @return FileInterface
     */
    public function execute(FileInterface $file, MetadataInterface $metadata): FileInterface
    {
        $segments = $file->getSegments();
        $pngIptcSegments = [];
        foreach ($segments as $key => $segment) {
            if ($this->isIptcSegment($segment)) {
                $pngIptcSegments[$key] = $segment;
            }
        }

        if (!is_callable('gzcompress') && !is_callable('gzuncompress')) {
            throw new LocalizedException(
                __('zlib gzcompress() && zlib gzuncompress() must be enabled in php configuration')
            );
        }

        if (empty($pngIptcSegments)) {
            return $this->fileFactory->create([
                'path' => $file->getPath(),
                'segments' => $this->insertPngIptcSegment($segments, $this->createPngIptcSegment($metadata))
            ]);
        }

        foreach ($pngIptcSegments as $key => $segment) {
            $segments[$key] = $this->updateIptcSegment($segment, $metadata);
        }

        return $this->fileFactory->create([
            'path' => $file->getPath(),
            'segments' => $segments
        ]);
    }

    /**
     * Insert IPTC segment to image png segments before IEND chunk
     *
     * @param SegmentInterface[] $segments
     * @param SegmentInterface $iptcSegment
     * @return SegmentInterface[]
     */
    private function insertPngIptcSegment(array $segments, SegmentInterface $iptcSegment): array
    {
        $iendSegmentIndex = count($segments) - 1;

        return array_merge(
            array_slice($segments, 0, $iendSegmentIndex),
            [$iptcSegment],
            array_slice($segments, $iendSegmentIndex)
        );
    }

    /**
     * Create new  zTXt segment with metadata
     *
     * @param MetadataInterface $metadata
     */
    private function createPngIptcSegment(MetadataInterface $metadata): SegmentInterface
    {
        $start = '8BIM' . str_repeat(pack('C', 4), 2) . str_repeat(pack("C", 0), 5)
            . 'c' . pack('C', 28) . pack('C', 1);
        $compression = 'Z' . pack('C', 0) . pack('C', 3) . pack('C', 27) . '%G' . pack('C', 28) . pack('C', 1);
        $end = str_repeat(pack('C', 0), 2) . pack('C', 2) . pack('C', 0) . pack('C', 4) . pack('C', 28);
        $binData = $start . $compression . $end;

        $description = $metadata->getDescription();
        if ($description !== null) {
            $descriptionMarker = pack("C", 2) . 'x' . pack("C", 0);
            $binData .= $descriptionMarker . pack('C', strlen($description)) . $description . pack('C', 28);
        }

        $title = $metadata->getTitle();
        if ($title !== null) {
            $titleMarker =  pack("C", 2) . 'i' . pack("C", 0);
            $binData .= $titleMarker . pack('C', strlen($title)) . $title . pack('C', 28);
        }

        $keywords = $metadata->getKeywords();
        if ($keywords !== null) {
            $keywordsMarker = pack("C", 2) . pack("C", 25) . pack("C", 0);
            $keywords = implode(',', $keywords);
            $binData .= $keywordsMarker . pack('C', strlen($keywords)) . $keywords . pack('C', 28);
        }

        $binData .= pack('C', 0);
        $hexString = bin2hex($binData);
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $compressedIptcData = gzcompress(PHP_EOL . 'iptc' . PHP_EOL . strlen($binData) . PHP_EOL . $hexString);

        return $this->segmentFactory->create([
            'name' => self::IPTC_SEGMENT_NAME,
            'data' => self::IPTC_SEGMENT_START_STRING . str_repeat(pack('C', 0), 2) . $compressedIptcData
        ]);
    }

    /**
     * Update iptc data to zTXt segment
     *
     * @param SegmentInterface $segment
     * @param MetadataInterface $metadata
     */
    private function updateIptcSegment(SegmentInterface $segment, MetadataInterface $metadata): SegmentInterface
    {
        $description = null;
        $title = null;
        $keywords = null;

        $iptSegmentStartPosition = strpos($segment->getData(), pack("C", 0) . pack("C", 0) . 'x');
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $uncompressedData = gzuncompress(substr($segment->getData(), $iptSegmentStartPosition + 2));

        $data = explode(PHP_EOL, trim($uncompressedData));
        //remove header and size from hex string
        $iptcData = implode(array_slice($data, 2));
        $binData = hex2bin($iptcData);

        if ($metadata->getDescription() !== null) {
            $description = $metadata->getDescription();
            $descriptionMarker = pack("C", 2) . 'x' . pack("C", 0);
            $descriptionStartPosition = strpos($binData, $descriptionMarker) + 3;
            $binData = substr_replace(
                $binData,
                pack("C", strlen($description)) . $description,
                $descriptionStartPosition
            ) . substr($binData, $descriptionStartPosition + 1 + ord(substr($binData, $descriptionStartPosition)));
        }

        if ($metadata->getTitle() !== null) {
            $title = $metadata->getTitle();
            $titleMarker =  pack("C", 2) . 'i' . pack("C", 0);
            $titleStartPosition = strpos($binData, $titleMarker) + 3;
            $binData = substr_replace(
                $binData,
                pack("C", strlen($title)) . $title,
                $titleStartPosition
            ) . substr($binData, $titleStartPosition + 1 + ord(substr($binData, $titleStartPosition)));
        }

        if ($metadata->getKeywords() !== null) {
            $keywords = implode(',', $metadata->getKeywords());
            $keywordsMarker = pack("C", 2) . pack("C", 25) . pack("C", 0);
            $keywordsStartPosition = strpos($binData, $keywordsMarker) + 3;
            $binData = substr_replace(
                $binData,
                pack("C", strlen($keywords)) . $keywords,
                $keywordsStartPosition
            ) . substr($binData, $keywordsStartPosition + 1 + ord(substr($binData, $keywordsStartPosition)));
        }
        $hexString = bin2hex($binData);
        $iptcSegmentStart = substr($segment->getData(), 0, $iptSegmentStartPosition + 2);
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $segmentDataCompressed = gzcompress(PHP_EOL . $data[0] . PHP_EOL . strlen($binData) . PHP_EOL . $hexString);

        return $this->segmentFactory->create([
            'name' => $segment->getName(),
            'data' => $iptcSegmentStart . $segmentDataCompressed
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
            && strncmp(
                substr($segment->getData(), self::IPTC_DATA_START_POSITION, 4),
                self::IPTC_SEGMENT_START,
                self::IPTC_DATA_START_POSITION
            ) == 0;
    }
}
