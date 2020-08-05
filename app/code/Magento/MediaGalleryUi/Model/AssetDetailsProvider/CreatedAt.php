<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model\AssetDetailsProvider;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryUi\Model\AssetDetailsProviderInterface;

/**
 * Provide asset created at date time
 */
class CreatedAt implements AssetDetailsProviderInterface
{
    /**
     * @var TimezoneInterface
     */
    private $dateTime;

    /**
     * @param TimezoneInterface $dateTime
     */
    public function __construct(
        TimezoneInterface $dateTime
    ) {
        $this->dateTime = $dateTime;
    }

    /**
     * Provide asset created at date time
     *
     * @param AssetInterface $asset
     * @return array
     * @throws \Exception
     */
    public function execute(AssetInterface $asset): array
    {
        return [
            'title' => __('Created'),
            'value' => $this->formatDate($asset->getCreatedAt())
        ];
    }

    /**
     * Format date to standard format
     *
     * @param string $date
     * @return string
     * @throws \Exception
     */
    private function formatDate(string $date): string
    {
        return $this->dateTime->formatDate($date, \IntlDateFormatter::SHORT, true);
    }
}
