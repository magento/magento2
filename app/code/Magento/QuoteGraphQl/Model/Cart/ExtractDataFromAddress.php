<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Extract the necessary address fields from an Address model
 */
class ExtractDataFromAddress
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     */
    public function __construct(ExtensibleDataObjectConverter $dataObjectConverter)
    {
        $this->dataObjectConverter = $dataObjectConverter;
    }

    /**
     * Converts Address model to flat array
     *
     * @param QuoteAddress $address
     * @return array
     */
    public function execute(QuoteAddress $address): array
    {
        $addressData = $this->dataObjectConverter->toFlatArray($address, [], AddressInterface::class);
        $addressData['model'] = $address;

        if ($address->getShippingMethod()) {
            list($carrierCode, $methodCode) = explode('_', $address->getShippingMethod(), 2);
            $shippingAmount = $address->getShippingAmount();
        }

        $addressData = array_merge($addressData, [
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
                'carrier_code' => $carrierCode ?? null,
                'method_code' => $methodCode ?? null,
                'label' => $address->getShippingDescription(),
                'amount' => $shippingAmount ?? null
            ],
            'items_weight' => $address->getWeight(),
            'customer_notes' => $address->getCustomerNotes()
        ]);

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
