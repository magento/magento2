<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

/**
 * Billing address management interface for guest carts.
 * @api
 */
interface GuestBillingAddressManagementInterface
{
    /**
     * Assign a specified billing address to a specified cart.
     *
     * @param string $cartId The cart ID.
     * @param \Magento\Quote\Api\Data\AddressInterface $address Billing address data.
     * @return int Address ID.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\InputException The specified cart ID or address data is not valid.
     */
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address);

    /**
     * Return the billing address for a specified quote.
     *
     * @param string $cartId The cart ID.
     * @return \Magento\Quote\Api\Data\AddressInterface Quote billing address object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function get($cartId);
}
