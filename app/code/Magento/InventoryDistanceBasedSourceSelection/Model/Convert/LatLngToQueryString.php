<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\Convert;

use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterface;

class LatLngToQueryString
{
    /**
     * Get latitude and longitude as string
     *
     * @param LatLngInterface $latLng
     * @return string
     */
    public function execute(LatLngInterface $latLng): string
    {
        return $latLng->getLat() . ',' . $latLng->getLng();
    }
}
