<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventorySourceSelection\Plugin;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventorySourceSelection\Model\DistanceProvider\GoogleMap\GetLatLngRequestFromSource;

/**
 * Compute latitude and longitude for a source if none is defined
 */
class ComputeSourceLatitudeAndLongitude
{
    /**
     * @var GetLatLngRequestFromSource
     */
    private $getLatLngRequestFromSource;

    /**
     * ComputeSourceLatitudeAndLongitude constructor.
     *
     * @param GetLatLngRequestFromSource $getLatLngRequestFromSource
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        GetLatLngRequestFromSource $getLatLngRequestFromSource
    ) {
        $this->getLatLngRequestFromSource = $getLatLngRequestFromSource;
    }

    /**
     * Calculate latitude and longitude using google map if api key is defined
     *
     * @param SourceRepositoryInterface $subject
     * @param SourceInterface $source
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        SourceRepositoryInterface $subject,
        SourceInterface $source
    ): array {
        if (!$source->getLongitude() && !$source->getLongitude()) {
            try {
                $latLng = $this->getLatLngRequestFromSource->execute($source);
                $source->setLatitude($latLng->getLat());
                $source->setLongitude($latLng->getLng());
            } catch (\Exception $e) {
                unset($e); // Silently fail geo coding
            }
        }

        return [$source];
    }
}
