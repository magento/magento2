<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Quote\Api\Data\AddressInterface;

class AddressComparator implements AddressComparatorInterface
{
    /**
     * @inheritDoc
     */
    public function isEqual(?AddressInterface $shippingAddress, ?AddressInterface $billingAddress): bool
    {
        if ($shippingAddress->getCustomerAddressId() !== null &&
            $billingAddress->getCustomerAddressId() !== null
        ) {
            $sameAsBillingFlag = ((int)$shippingAddress->getCustomerAddressId() ===
                (int)$billingAddress->getCustomerAddressId());
        } else {
            $quoteShippingAddressData = $shippingAddress->getData();
            $billingAddressData = $billingAddress->getData();
            if (!empty($quoteShippingAddressData) && !empty($billingAddressData)) {
                $billingKeys = array_flip(array_keys($billingAddressData));
                $shippingData = array_intersect_key($quoteShippingAddressData, $billingKeys);
                $removeKeys = ['region_code', 'save_in_address_book'];
                $billingData = array_diff_key($billingAddressData, array_flip($removeKeys));
                $diff = array_udiff(
                    $billingData,
                    $shippingData,
                    function ($el1, $el2) {
                        if (is_object($el1)) {
                            $el1 = $this->serializer->serialize($el1);
                        }
                        if (is_object($el2)) {
                            $el2 = $this->serializer->serialize($el2);
                        }
                        return strcmp((string)$el1, (string)$el2);
                    }
                );
                $sameAsBillingFlag = empty($diff);
            } else {
                $sameAsBillingFlag = false;
            }
        }

        return $sameAsBillingFlag;
    }
}
