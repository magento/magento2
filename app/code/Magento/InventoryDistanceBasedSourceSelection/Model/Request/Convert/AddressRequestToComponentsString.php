<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\Request\Convert;

use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\AddressRequestInterface;

class AddressRequestToComponentsString
{
    /**
     * Get components string from address
     *
     * @param AddressRequestInterface $addressRequest
     * @return string
     */
    public function execute(AddressRequestInterface $addressRequest): string
    {
        return implode('|', [
            'country:' . $addressRequest->getCountry(),
            'postal_code:' . $addressRequest->getPostcode(),
            'locality:' . $addressRequest->getCity(),
            'administrative_area:' . $addressRequest->getRegion(),
        ]);
    }
}
