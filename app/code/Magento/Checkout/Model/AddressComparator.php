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
            $diff = $this->arrayDiffAssocRecursive($billingData, $shippingData);
            return empty($diff);
        }
    }

    /**
     * Compare two arrays
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function arrayDiffAssocRecursive(array $array1, array $array2): array
    {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key])) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->arrayDiffAssocRecursive($value, $array2[$key]);
                    if (!empty($new_diff)) {
                        $difference[$key] = $new_diff;
                    }
                }
            } else {
                $str1 = is_object($array2[$key]) ? $this->serializer->serialize($array2[$key]) : (string)$array2[$key];
                $str2 = is_object($array2[$key]) ? $this->serializer->serialize($value) : (string)$value;
                if ((!empty($str1) && !empty($str2) && $str1 !== $str2)
                    || (!empty($str1) && empty($str2))
                    || (!empty($str1) && empty($str2))) {
                    $difference[$key] = $str2;
                }
            }
        }
        return $difference;
    }
}
