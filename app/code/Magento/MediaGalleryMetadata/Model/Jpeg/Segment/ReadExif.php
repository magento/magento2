<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model\Jpeg\Segment;

use Magento\Framework\App\ObjectManager;
use Magento\MediaGalleryMetadata\Model\ExifReader;
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
    private const EXIF_SEGMENT_NAME = 'APP1';
    private const EXIF_SEGMENT_START = "Exif\x00";
    private const EXIF_DATA_START_POSITION = 0;

    /**
     * @var MetadataInterfaceFactory
     */
    private $metadataFactory;

    /**
     * @var ExifReader
     */
    private $exifReader;

    /**
     * @param MetadataInterfaceFactory $metadataFactory
     * @param ExifReader|null $exifReader
     */
    public function __construct(
        MetadataInterfaceFactory $metadataFactory,
        ExifReader $exifReader = null
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->exifReader = $exifReader ?? ObjectManager::getInstance()->get(ExifReader::class);
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
                return $this->getExifData($file->getPath());
            }
        }

        return $this->metadataFactory->create([
            'title' => null,
            'description' => null,
            'keywords' => null
        ]);
    }

    /**
     * Parse exif data from segment
     *
     * @param string $filePath
     */
    private function getExifData(string $filePath): MetadataInterface
    {
        $title = null;
        $description = null;
        $keywords = null;

        $data = $this->exifReader->get($filePath);

        if (!empty($data)) {
            $title = isset($data['DocumentName']) ? $data['DocumentName'] : null;
            $description = isset($data['ImageDescription']) ? $data['ImageDescription'] : null;
        }

        return $this->metadataFactory->create([
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords
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
        return $segment->getName() === self::EXIF_SEGMENT_NAME
            && strncmp(
                substr($segment->getData(), self::EXIF_DATA_START_POSITION, 5),
                self::EXIF_SEGMENT_START,
                5
            ) == 0;
    }
}
