<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionApi\Model\DistanceProvider;

use Magento\InventoryDistanceBasedSourceSelectionApi\Model\LatLngInterface;

/**
 * Get distance between two LatLngRequest points
 *
 * @api
 */
interface GetDistanceInterface
{
    /**
     * Get distance between two points
     *
     * @param LatLngInterface $source
     * @param LatLngInterface $destination
     * @return float
     */
    public function execute(LatLngInterface $source, LatLngInterface $destination): float;
}
