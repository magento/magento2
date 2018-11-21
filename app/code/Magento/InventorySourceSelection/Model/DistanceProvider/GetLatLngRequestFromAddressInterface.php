<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\DistanceProvider;

use Magento\InventorySourceSelection\Model\Request\LatLngRequest;
use Magento\InventorySourceSelectionApi\Api\Data\AddressRequestInterface;

/**
 * Get latitude and longitude from address request
 */
interface GetLatLngRequestFromAddressInterface
{
    /**
     * Get latitude and longitude from address
     *
     * @param AddressRequestInterface $addressRequest
     * @return LatLngRequest
     */
    public function execute(AddressRequestInterface $addressRequest): LatLngRequest;
}
