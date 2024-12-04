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
    public function isEqual(?AddressInterface $address1, ?AddressInterface $address2): bool
    {
        if ($address1 === null || $address2 === null) {
            return false;
        }

        if ($address1->getCustomerAddressId() !== null &&
            $address2->getCustomerAddressId() !== null
        ) {
            return ((int)$address1->getCustomerAddressId() ===
                (int)$address2->getCustomerAddressId());
        } else {
            $addressKeys = array_intersect_key($address1->getData(), $address2->getData());
            $removeKeys = ['address_type', 'region_code', 'save_in_address_book', 'customer_address_id'];
            $addressKeys = array_diff_key($addressKeys, array_flip($removeKeys));

            $address1Data = array_intersect_key($address1->getData(), $addressKeys);
            $address2Data = array_intersect_key($address2->getData(), $addressKeys);
            $diff = $this->computeArrayDifference($address1Data, $address2Data);
            return empty($diff);
        }
    }

    /**
     * Computing the difference of two arrays
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function computeArrayDifference(array $array1, array $array2): array
    {
        return array_udiff_assoc(
            $array1,
            $array2,
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
    }
}
