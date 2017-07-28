<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

/**
 * Interface ShippingAddressManagementInterface
 * @api
 * @since 2.0.0
 */
interface ShippingAddressManagementInterface
{
    /**
     * Assigns a specified shipping address to a specified cart.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Quote\Api\Data\AddressInterface $address The shipping address data.
     * @return int Address ID.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\InputException The specified cart ID or address data is not valid.
     * @since 2.0.0
     */
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address);

    /**
     * Returns the shipping address for a specified quote.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Quote\Api\Data\AddressInterface Shipping address object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @since 2.0.0
     */
    public function get($cartId);
}
