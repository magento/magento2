<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Address;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Class AddressDataProvider
 *
 * Collect and return information about cart shipping and billing addresses
 */
class AddressDataProvider
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * AddressDataProvider constructor.
     *
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     */
    public function __construct(
        ExtensibleDataObjectConverter $dataObjectConverter
    ) {
        $this->dataObjectConverter = $dataObjectConverter;
    }

    /**
     * Collect and return information about shipping addresses
     *
     * @param CartInterface $cart
     * @return array
     */
    public function getShippingAddresses(CartInterface $cart): array
    {
        $addressData = [];
        $shippingAddress = $cart->getShippingAddress();

        if ($shippingAddress) {
            $shippingData = $this->dataObjectConverter->toFlatArray($shippingAddress, [], AddressInterface::class);
            $shippingMethodData = explode('_', $shippingAddress->getShippingMethod());
            $shippingData['selected_shipping_method'] = [
                'carrier_code' => $shippingMethodData[0],
                'method_code' => $shippingMethodData[1],
                'label' => $shippingAddress->getShippingDescription(),
                'free_shipping' => $shippingAddress->getFreeShipping()
            ];
            $addressData['shipping_addresses'] = array_merge($shippingData, $this->extractAddressData($shippingAddress));
        }

        return $addressData;
    }

    /**
     * Collect and return information about billing address
     *
     * @param CartInterface $cart
     * @return array
     */
    public function getBillingAddress(CartInterface $cart): array
    {
        $addressData = [];
        $billingAddress = $cart->getBillingAddress();

        if ($billingAddress) {
            $billingData = $this->dataObjectConverter->toFlatArray($billingAddress, [], AddressInterface::class);
            $addressData['billing_address'] = array_merge($billingData, $this->extractAddressData($billingAddress));
        }

        return $addressData;
    }

    /**
     * Extract the necessary address fields from address model
     *
     * @param QuoteAddress $address
     * @return array
     */
    private function extractAddressData(QuoteAddress $address): array
    {
        $addressData = [
            'cart_address_id' => $address->getId(),
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
        ];

        return $addressData;
    }
}
