<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventorySourceSelection\Plugin;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventorySourceSelectionApi\Model\GetGeoReferenceProvider;

/**
 * Compute latitude and longitude for a source if none is defined
 */
class FillSourceLatitudeAndLongitude
{
    /**
     * @var GetGeoReferenceProvider
     */
    private $getGeoReferenceProvider;

    /**
     * ComputeSourceLatitudeAndLongitude constructor.
     *
     * @param GetGeoReferenceProvider $getGeoReferenceProvider
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        GetGeoReferenceProvider $getGeoReferenceProvider
    ) {
        $this->getGeoReferenceProvider = $getGeoReferenceProvider;
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
        if (!$source->getLatitude() && !$source->getLongitude()) {
            try {
                $geoReferenceProvider = $this->getGeoReferenceProvider->execute();
                $latLng = $geoReferenceProvider->getSourceLatLng($source);

                $source->setLatitude($latLng->getLat());
                $source->setLongitude($latLng->getLng());
            } catch (\Exception $e) {
                unset($e); // Silently fail geo coding
            }
        }

        return [$source];
    }
}
