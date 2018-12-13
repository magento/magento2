<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\Request\Convert;

use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\AddressRequestInterface;

class AddressRequestToString
{
    /**
     * Get string from address
     *
     * @param AddressRequestInterface $addressRequest
     * @return string
     */
    public function execute(AddressRequestInterface $addressRequest): string
    {
        return implode(' ', [
            $addressRequest->getStreetAddress(),
            $addressRequest->getPostcode(),
            $addressRequest->getCity(),
            $addressRequest->getRegion(),
            $addressRequest->getCountry(),
        ]);
    }
}
