<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionApi\Model\DistanceProvider;

use Magento\InventoryDistanceBasedSourceSelectionApi\Model\Request\LatLngRequestInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\AddressRequestInterface;

/**
 * Get latitude and longitude from address request
 *
 * @api
 */
interface GetLatLngRequestFromAddressInterface
{
    /**
     * Get latitude and longitude from address
     *
     * @param AddressRequestInterface $addressRequest
     * @return LatLngRequestInterface
     */
    public function execute(AddressRequestInterface $addressRequest): LatLngRequestInterface;
}
