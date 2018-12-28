<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data;

/**
 * DTO for latitude and longitude request
 *
 * @api
 */
interface LatLngInterface
{
    /**
     * Get latitude
     *
     * @return float
     */
    public function getLat(): float;

    /**
     * Get longitude
     *
     * @return float
     */
    public function getLng(): float;
}
