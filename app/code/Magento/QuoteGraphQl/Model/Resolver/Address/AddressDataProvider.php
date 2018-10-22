<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\Address;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;

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
            $shippingData['selected_shipping_method'] = [
                'code' => $shippingAddress->getShippingMethod(),
                'label' => $shippingAddress->getShippingDescription(),
                'free_shipping' => $shippingAddress->getFreeShipping(),
            ];
            $shippingData['items_weight'] = $shippingAddress->getWeight();
            $shippingData['customer_notes'] = $shippingAddress->getCustomerNotes();
            $addressData[] = $shippingData;
        }

        if ($billingAddress) {
            $billingData = $this->dataObjectConverter->toFlatArray($billingAddress, [], AddressInterface::class);
            $billingData['address_type'] = 'BILLING';
            $addressData[] = $billingData;
        }

        return $addressData;
    }
}
