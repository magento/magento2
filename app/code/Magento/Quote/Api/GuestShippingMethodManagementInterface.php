<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

/**
 * Shipping method management interface for guest carts.
 * @api
 * @since 100.0.2
 */
interface GuestShippingMethodManagementInterface
{
    /**
     * List applicable shipping methods for a specified quote.
     *
     * @param string $cartId The shopping cart ID.
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified quote does not exist.
     * @throws \Magento\Framework\Exception\StateException The shipping address is missing.
     */
    public function getList($cartId);

    /**
     * Estimate shipping
     *
     * @param string $cartId The shopping cart ID.
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address The estimate address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     * @deprecated 100.0.7
     */
    public function estimateByAddress($cartId, \Magento\Quote\Api\Data\EstimateAddressInterface $address);
}
