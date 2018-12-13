<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\Request\Convert;

use Magento\InventoryDistanceBasedSourceSelectionApi\Model\Request\LatLngRequestInterface;

class LatLngRequestToQueryString
{
    /**
     * Get latitude and longitude as string
     *
     * @param LatLngRequestInterface $latLngRequest
     * @return string
     */
    public function execute(LatLngRequestInterface $latLngRequest): string
    {
        return $latLngRequest->getLat() . ',' . $latLngRequest->getLng();
    }
}
