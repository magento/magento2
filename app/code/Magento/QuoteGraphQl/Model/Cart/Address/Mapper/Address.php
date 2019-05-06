<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Address\Mapper;

use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Class Address
 *
 * Extract the necessary address fields from an Address model
 */
class Address
{
    /**
     * Converts Address model data to nested array
     *
     * @param QuoteAddress $address
     * @return array
     */
    public function toNestedArray(QuoteAddress $address): array
    {
        $addressData = [
            'country' => [
                'code' => $address->getCountryId(),
                'label' => $address->getCountry()
            ],
            'region' => [
                'code' => $address->getRegionCode(),
                'label' => $address->getRegion()
            ],
            'street' => $address->getStreet(),
            'selected_shipping_method' => [
                'code' => $address->getShippingMethod(),
                'label' => $address->getShippingDescription(),
                'free_shipping' => $address->getFreeShipping(),
            ],
            'items_weight' => $address->getWeight(),
            'customer_notes' => $address->getCustomerNotes()
        ];

        if (!$address->hasItems()) {
            return $addressData;
        }

        $addressItemsData = [];
        foreach ($address->getAllItems() as $addressItem) {
            $addressItemsData[] = [
                'cart_item_id' => $addressItem->getQuoteItemId(),
                'quantity' => $addressItem->getQty()
            ];
        }
        $addressData['cart_items'] = $addressItemsData;

        return $addressData;
    }
}
