<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\MediaGalleryMetadata\Model\Jpeg\ReadFile;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;

/**
 * Write iptc data to the file return updated FileInterface with iptc data
 */
class AddIptcMetadata
{
    private const IPTC_TITLE_SEGMENT = '2#005';
    private const IPTC_DESCRIPTION_SEGMENT = '2#120';
    private const IPTC_KEYWORDS_SEGMENT = '2#025';

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var ReadFile
     */
    private $fileReader;

    /**
     * @var FileInterfaceFactory
     */
    private $fileFactory;

    /**
     * @param FileInterfaceFactory $fileFactory
     * @param DriverInterface $driver
     * @param ReadFile $fileReader
     */
    public function __construct(
        FileInterfaceFactory $fileFactory,
        DriverInterface $driver,
        ReadFile $fileReader
    ) {
        $this->fileFactory = $fileFactory;
        $this->driver = $driver;
        $this->fileReader = $fileReader;
    }

    /**
     * Write metadata
     *
     * @param FileInterface $file
     * @param MetadataInterface $metadata
     * @param null|SegmentInterface $segment
     */
    public function execute(FileInterface $file, MetadataInterface $metadata, ?SegmentInterface $segment): FileInterface
    {
        if (!is_callable('iptcembed') && !is_callable('iptcparse')) {
            throw new LocalizedException(__('iptcembed() && iptcparse() must be enabled in php configuration'));
        }

        $iptcData =  $segment ? iptcparse($segment->getData()) : [];

        if ($metadata->getTitle() !== null) {
            $iptcData[self::IPTC_TITLE_SEGMENT][0] = $metadata->getTitle();
        }

        if ($metadata->getDescription() !== null) {
            $iptcData[self::IPTC_DESCRIPTION_SEGMENT][0] = $metadata->getDescription();
        }

        if ($metadata->getKeywords() !== null) {
            $iptcData = $this->writeKeywords($metadata->getKeywords(), $iptcData);
        }

        $newData = '';

        foreach ($iptcData as $tag => $values) {
            foreach ($values as $value) {
                $newData .= $this->iptcMaketag(2, (int) substr($tag, 2), $value);
            }
        }

        $this->writeFile($file->getPath(), iptcembed($newData, $file->getPath()));

        $fileWithIptc = $this->fileReader->execute($file->getPath());

        return $this->fileFactory->create([
                'path' => $fileWithIptc->getPath(),
                'segments' => $this->getSegmentsWithIptc($fileWithIptc, $file)
        ]);
    }

    /**
     * Return iptc segment from file.
     *
     * @param FileInterface $fileWithIptc
     * @param FileInterface $originFile
     */
    private function getSegmentsWithIptc(FileInterface $fileWithIptc, $originFile): array
    {
        $segments = $fileWithIptc->getSegments();
        $originFileSegments =  $originFile->getSegments();

        foreach ($segments as $key => $segment) {
            if ($segment->getName() === 'APP13') {
                foreach ($originFileSegments as $originKey => $segment) {
                    if ($segment->getName() === 'APP13') {
                        $originFileSegments[$originKey] = $segments[$key];
                    }
                }
                return $originFileSegments;
            }
        }
        return $originFileSegments;
    }

    /**
     * Write keywords field to the iptc segment.
     *
     * @param array $keywords
     * @param array $iptcData
     */
    private function writeKeywords(array $keywords, array $iptcData): array
    {
        foreach ($keywords as $key => $keyword) {
            $iptcData[self::IPTC_KEYWORDS_SEGMENT][$key] = $keyword;
        }
        return $iptcData;
    }

    /**
     * Write iptc data to the image directly to the file.
     *
     * @param string $filePath
     * @param string $content
     */
    private function writeFile(string $filePath, string $content): void
    {
        $resource = $this->driver->fileOpen($filePath, 'wb');

        $this->driver->fileWrite($resource, $content);
        $this->driver->fileClose($resource);
    }

    /**
     * Create new iptc tag text
     *
     * @param int $rec
     * @param int $tag
     * @param string $value
     */
    private function iptcMaketag(int $rec, int $tag, string $value)
    {
        //phpcs:disable Magento2.Functions.DiscouragedFunction
        $length = strlen($value);
        $retval = chr(0x1C) . chr($rec) . chr($tag);

        if ($length < 0x8000) {
            $retval .= chr($length >> 8) . chr($length & 0xFF);
        } else {
            $retval .= chr(0x80) .
                   chr(0x04) .
                   chr(($length >> 24) & 0xFF) .
                   chr(($length >> 16) & 0xFF) .
                   chr(($length >> 8) & 0xFF) .
                   chr($length & 0xFF);
        }
        //phpcs:enable Magento2.Functions.DiscouragedFunction
        return $retval . $value;
    }
}
