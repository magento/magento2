<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;

/**
 * Provides asset detail for view details section
 */
class GetAssetDetails
{
    /**
     * @var TimezoneInterface
     */
    private $dateTime;

    /**
     * @var GetAssetUsageDetails
     */
    private $getAssetUsageDetails;

    /**
     * @param GetAssetUsageDetails $getAssetUsageDetails
     * @param TimezoneInterface $dateTime
     */
    public function __construct(
        GetAssetUsageDetails $getAssetUsageDetails,
        TimezoneInterface $dateTime
    ) {
        $this->dateTime = $dateTime;
        $this->getAssetUsageDetails = $getAssetUsageDetails;
    }

    /**
     * Get a piece of asset details
     *
     * @param AssetInterface $asset
     * @return array
     */
    public function execute(AssetInterface $asset): array
    {
        $details = [
            [
                'title' => __('Type'),
                'value' => __('Image'),
            ],
            [
                'title' => __('Created'),
                'value' => $this->formatDate($asset->getCreatedAt())
            ],
            [
                'title' => __('Modified'),
                'value' => $this->formatDate($asset->getUpdatedAt())
            ],
            [
                'title' => __('Width'),
                'value' => sprintf('%spx', $asset->getWidth())
            ],
            [
                'title' => __('Height'),
                'value' => sprintf('%spx', $asset->getHeight())
            ],
            [
                'title' => __('Size'),
                'value' => $this->formatSize($asset->getSize())
            ],
            [
                'title' => __('Used In'),
                'value' => $this->getAssetUsageDetails->execute($asset->getId())
            ]
        ];
        return $details;
    }

    /**
     * Format image size
     *
     * @param int $size
     * @return string
     */
    private function formatSize(int $size): string
    {
        return $size === 0 ? '' : sprintf('%.2f KB', $size / 1024);
    }

    /**
     * Format date to standard format
     *
     * @param string $date
     * @return string
     */
    private function formatDate(string $date): string
    {
        return $this->dateTime->formatDate($date, \IntlDateFormatter::SHORT, true);
    }
}
