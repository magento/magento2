<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\AddressInterface;

class AddressComparator implements AddressComparatorInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * AddressComparator constructor
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function isEqual(?AddressInterface $shippingAddress, ?AddressInterface $billingAddress): bool
    {
        if ($shippingAddress === null || $billingAddress === null) {
            return false;
        }

        if ($shippingAddress->getCustomerAddressId() !== null &&
            $billingAddress->getCustomerAddressId() !== null
        ) {
            return ((int)$shippingAddress->getCustomerAddressId() ===
                (int)$billingAddress->getCustomerAddressId());
        } else {
            $shippingAddressData = $shippingAddress->getData();
            $billingAddressData = $billingAddress->getData();
            $billingKeys = array_flip(array_keys($billingAddressData));
            $shippingData = array_intersect_key($shippingAddressData, $billingKeys);
            $removeKeys = ['address_type', 'region_code', 'save_in_address_book'];
            $billingData = array_diff_key($billingAddressData, array_flip($removeKeys));
            $diff = array_udiff(
                $billingData,
                $shippingData,
                function ($el1, $el2) {
                    if (is_object($el1) || is_array($el1)) {
                        $el1 = $this->serializer->serialize($el1);
                    }
                    if (is_object($el2) || is_array($el2)) {
                        $el2 = $this->serializer->serialize($el2);
                    }
                    return strcmp((string)$el1, (string)$el2);
                }
            );
            return empty($diff);
        }
    }
}
