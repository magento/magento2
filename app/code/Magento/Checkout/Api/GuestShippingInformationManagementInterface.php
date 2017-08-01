<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api;

/**
 * Interface for managing guest shipping address information
 * @api
 * @since 2.0.0
 */
interface GuestShippingInformationManagementInterface
{
    /**
     * @param string $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     * @since 2.0.0
     */
    public function saveAddressInformation(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    );
}
