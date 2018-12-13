<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\Request\Convert;

use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\AddressRequestInterface;

class AddressRequestToQueryString
{
    /**
     * Get components string from address
     *
     * @param AddressRequestInterface $addressRequest
     * @return string
     */
    public function execute(AddressRequestInterface $addressRequest): string
    {
        return
            $addressRequest->getStreetAddress() . ', ' .
            $addressRequest->getPostcode() . ' ' .
            $addressRequest->getCity();
    }
}
