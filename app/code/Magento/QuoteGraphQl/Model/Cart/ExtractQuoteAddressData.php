<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Extract address fields from an Quote Address model
 */
class ExtractQuoteAddressData
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

        if ($address->getAddressType() == AbstractAddress::TYPE_SHIPPING) {
            $addressType = 'SHIPPING';
        } elseif ($address->getAddressType() == AbstractAddress::TYPE_BILLING) {
            $addressType = 'BILLING';
        } else {
            $addressType = null;
        }

        $addressData = array_merge($addressData, [
            'address_type' => $addressType,
            'country' => [
                'code' => $address->getCountryId(),
                'label' => $address->getCountry()
            ],
            'region' => [
                'code' => $address->getRegionCode(),
                'label' => $address->getRegion()
            ],
            'street' => $address->getStreet(),
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
