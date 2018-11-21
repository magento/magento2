<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\DistanceProvider;

use Magento\InventorySourceSelection\Model\Request\LatLngRequest;

/**
 * Get distance between two LatLngRequest points
 */
interface GetDistanceInterface
{
    /**
     * Get distance between two points
     *
     * @param LatLngRequest $source
     * @param LatLngRequest $destination
     * @return float
     */
    public function execute(LatLngRequest $source, LatLngRequest $destination): float;
}
