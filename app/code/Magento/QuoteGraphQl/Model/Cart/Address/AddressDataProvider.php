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
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Cart\ShippingMethodConverter;

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
     * @var ShippingMethodConverter
     */
    private $shippingMethodConverter;

    /**
     * AddressDataProvider constructor.
     *
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     * @param ShippingMethodConverter $shippingMethodConverter
     */
    public function __construct(
        ExtensibleDataObjectConverter $dataObjectConverter,
        ShippingMethodConverter $shippingMethodConverter
    ) {
        $this->dataObjectConverter = $dataObjectConverter;
        $this->shippingMethodConverter = $shippingMethodConverter;
    }

    /**
     * Collect and return information about shipping and billing addresses
     *
     * @param CartInterface $cart
     * @return array
     */
    public function getCartAddresses(CartInterface $cart): array
    {
        $addressData = [];
        $shippingAddress = $cart->getShippingAddress();
        $billingAddress = $cart->getBillingAddress();

        if ($shippingAddress) {
            $shippingData = $this->dataObjectConverter->toFlatArray($shippingAddress, [], AddressInterface::class);
            $shippingData['address_type'] = 'SHIPPING';
            $addressData[] = array_merge($shippingData, $this->extractAddressData($shippingAddress));
        }

        if ($billingAddress) {
            $billingData = $this->dataObjectConverter->toFlatArray($billingAddress, [], AddressInterface::class);
            $billingData['address_type'] = 'BILLING';
            $addressData[] = array_merge($billingData, $this->extractAddressData($billingAddress));
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
            'model' => $address,
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
                'amount' => $address->getShippingAmount(),
                'base_amount' => $address->getBaseShippingAmount(),
                'amount_incl_tax' => $address->getShippingInclTax(),
                'base_amount_incl_tax' => $address->getBaseShippingInclTax(),
            ],
            'items_weight' => $address->getWeight(),
            'customer_notes' => $address->getCustomerNotes(),
            'quote_id' => $address->getQuoteId(),
        ];

        return $addressData;
    }

    private function extractAvailableShippingRateData(CartInterface $cart, QuoteAddress $address): array
    {
        $output = [];

        // Allow shipping rates by setting country id for new addresses
        if (!$address->getCountryId() && $address->getCountryCode()) {
            $address->setCountryId($address->getCountryCode());
        }

        $address->setCollectShippingRates(true);
        $address->collectShippingRates();

        $shippingRates = $address->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->dataObjectConverter->toFlatArray(
                    $this->shippingMethodConverter->modelToDataObject($rate, $cart->getQuoteCurrencyCode()),
                    [],
                    ShippingMethodInterface::class
                );
            }
        }

        return $output;
    }
}
