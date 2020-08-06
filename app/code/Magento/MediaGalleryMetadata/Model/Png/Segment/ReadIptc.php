<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Png\Segment;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\ReadMetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;

/**
 * IPTC Reader to read IPTC data for png image
 */
class ReadIptc implements ReadMetadataInterface
{
    private const IPTC_SEGMENT_NAME = 'zTXt';
    private const IPTC_SEGMENT_START = 'iptc';
    private const IPTC_DATA_START_POSITION = 17;
    private const IPTC_CHUNK_MARKER_LENGTH = 4;

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
        foreach ($file->getSegments() as $segment) {
            if ($this->isIptcSegment($segment)) {
                if (!is_callable('gzcompress') && !is_callable('gzuncompress')) {
                    throw new LocalizedException(
                        __('zlib gzcompress() && zlib gzuncompress() must be enabled in php configuration')
                    );
                }
                return $this->getIptcData($segment);
            }
        }

        return $this->metadataFactory->create([
            'title' => null,
            'description' => null,
            'keywords' => null
        ]);
    }

    /**
     * Read iptc data from zTXt segment
     *
     * @param SegmentInterface $segment
     */
    private function getIptcData(SegmentInterface $segment): MetadataInterface
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

        $descriptionMarker = pack("C", 2) . 'x' . pack("C", 0);
        $descriptionStartPosition = strpos($binData, $descriptionMarker);
        if ($descriptionStartPosition) {
            $description = substr(
                $binData,
                $descriptionStartPosition + self::IPTC_CHUNK_MARKER_LENGTH,
                ord(substr($binData, $descriptionStartPosition + 3, 1))
            );
        }

        $titleMarker =  pack("C", 2) . 'i' . pack("C", 0);
        $titleStartPosition = strpos($binData, $titleMarker);
        if ($titleStartPosition) {
            $title = substr(
                $binData,
                $titleStartPosition + self::IPTC_CHUNK_MARKER_LENGTH,
                ord(substr($binData, $titleStartPosition + 3, 1))
            );
        }

        $keywordsMarker = pack("C", 2) . pack("C", 25) . pack("C", 0);
        $keywordsStartPosition = strpos($binData, $keywordsMarker);
        if ($keywordsStartPosition) {
            $keywords = substr(
                $binData,
                $keywordsStartPosition + self::IPTC_CHUNK_MARKER_LENGTH,
                ord(substr($binData, $keywordsStartPosition + 3, 1))
            );
        }

        return $this->metadataFactory->create([
            'title' => $title,
            'description' => $description,
            'keywords' => !empty($keywords) ? explode(',', $keywords) : null
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
