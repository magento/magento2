<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model;

use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterfaceFactory;

/**
 * Get metadata from IPTC block
 */
class GetIptcMetadata
{
    private const IPTC_TITLE = '2#005';
    private const IPTC_DESCRIPTION = '2#120';
    private const IPTC_KEYWORDS = '2#025';

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
     * Parse metadata
     *
     * @param string $data
     * @return MetadataInterface
     */
    public function execute(string $data): MetadataInterface
    {
        $title = null;
        $description = null;
        $keywords = [];

        if (is_callable('iptcparse')) {
            $iptcData = iptcparse($data);

            if (!empty($iptcData[self::IPTC_TITLE][0])) {
                $title = trim($iptcData[self::IPTC_TITLE][0]);
            }

            if (!empty($iptcData[self::IPTC_DESCRIPTION][0])) {
                $description = trim($iptcData[self::IPTC_DESCRIPTION][0]);
            }

            if (!empty($iptcData[self::IPTC_KEYWORDS][0])) {
                $keywords = array_values($iptcData[self::IPTC_KEYWORDS]);
            }
        }

        return $this->metadataFactory->create([
            'title' => $title,
            'description' => $description,
            'keywords' => !empty($keywords) ? $keywords : null
        ]);
    }
}
