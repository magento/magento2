<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Plugin;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider\GetLatLngFromSource;
use Magento\InventoryDistanceBasedSourceSelectionApi\Model\GetLatLngFromSourceInterface;

/**
 * Compute latitude and longitude for a source if none is defined
 */
class FillSourceLatitudeAndLongitude
{
    /**
     * @var GetLatLngFromSource
     */
    private $getLatLngFromSource;

    /**
     * ComputeSourceLatitudeAndLongitude constructor.
     *
     * @param GetLatLngFromSource $getLatLngFromSource
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        GetLatLngFromSource $getLatLngFromSource
    ) {
        $this->getLatLngFromSource = $getLatLngFromSource;
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
                $latLng = $this->getLatLngFromSource->execute($source);

                $source->setLatitude($latLng->getLat());
                $source->setLongitude($latLng->getLng());
            } catch (\Exception $e) {
                unset($e); // Silently fail geo coding
            }
        }

        return [$source];
    }
}
