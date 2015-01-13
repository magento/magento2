<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api;

interface ShippingAddressManagementInterface
{
    /**
     * Assigns a specified shipping address to a specified cart.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Checkout\Api\Data\AddressInterface $address The shipping address data.
     * @return int Address ID.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\InputException The specified cart ID or address data is not valid.
     * @see \Magento\Checkout\Service\V1\Address\Shipping\WriteServiceInterface::setAddress
     */
    public function assign($cartId, \Magento\Checkout\Api\Data\AddressInterface $address);

    /**
     * Returns the shipping address for a specified quote.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Api\Data\AddressInterface Shipping address object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @see \Magento\Checkout\Service\V1\Address\Shipping\ReadServiceInterface::getAddress
     */
    public function get($cartId);
}
