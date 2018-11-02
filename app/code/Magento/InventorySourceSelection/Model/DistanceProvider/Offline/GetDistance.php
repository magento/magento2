<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\DistanceProvider\Offline;

use Magento\InventorySourceSelection\Model\Request\LatLngRequest;

class GetDistance
{
    const EARTH_RADIUS = 6371000.0;

    /**
     * Get distance between two points
     *
     * @param LatLngRequest $source
     * @param LatLngRequest $destination
     * @return float
     */
    public function execute(LatLngRequest $source, LatLngRequest $destination): float
    {
        $latFrom = deg2rad($source->getLat());
        $lonFrom = deg2rad($source->getLng());
        $latTo = deg2rad($destination->getLat());
        $lonTo = deg2rad($destination->getLng());

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * (float) self::EARTH_RADIUS;
    }
}
