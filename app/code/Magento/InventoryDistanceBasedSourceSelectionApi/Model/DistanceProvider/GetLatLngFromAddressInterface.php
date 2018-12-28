<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionApi\Model\DistanceProvider;

use Magento\InventoryDistanceBasedSourceSelectionApi\Model\LatLngInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\AddressInterface;

/**
 * Get latitude and longitude from address
 *
 * @api
 */
interface GetLatLngFromAddressInterface
{
    /**
     * Get latitude and longitude from address
     *
     * @param AddressInterface $address
     * @return LatLngInterface
     */
    public function execute(AddressInterface $address): LatLngInterface;
}
