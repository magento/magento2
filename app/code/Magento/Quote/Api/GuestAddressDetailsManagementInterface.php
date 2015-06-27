<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

interface GuestAddressDetailsManagementInterface
{
    /**
     * Save billing and shipping addresses for guest.
     *
     * @param string $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $billingAddress
     * @param \Magento\Quote\Api\Data\AddressInterface|null $shippingAddress
     * @param \Magento\Quote\Api\Data\AddressAdditionalDataInterface|null $additionalData
     * @return \Magento\Quote\Api\Data\AddressDetailsInterface
     */
    public function saveAddresses(
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress,
        \Magento\Quote\Api\Data\AddressInterface $shippingAddress = null,
        \Magento\Quote\Api\Data\AddressAdditionalDataInterface $additionalData = null
    );
}
