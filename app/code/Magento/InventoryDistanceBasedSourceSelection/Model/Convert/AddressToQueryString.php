<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\Convert;

use Magento\InventorySourceSelectionApi\Api\Data\AddressInterface;

class AddressToQueryString
{
    /**
     * Get components string from address
     *
     * @param AddressInterface $address
     * @return string
     */
    public function execute(AddressInterface $address): string
    {
        return
            $address->getStreet() . ', ' .
            $address->getPostcode() . ' ' .
            $address->getCity();
    }
}
