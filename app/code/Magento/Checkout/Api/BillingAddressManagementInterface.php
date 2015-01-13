<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api;

interface BillingAddressManagementInterface
{
    /**
     * Assigns a specified billing address to a specified cart.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Checkout\Api\Data\AddressInterface $address Billing address data.
     * @return int Address ID.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\InputException The specified cart ID or address data is not valid.
     * @see \Magento\Checkout\Service\V1\Address\Billing\WriteServiceInterface::setAddress
     */
    public function assign($cartId, \Magento\Checkout\Api\Data\AddressInterface $address);

    /**
     * Returns the billing address for a specified quote.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Api\Data\AddressInterface Quote billing address object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @see \Magento\Checkout\Service\V1\Address\Billing\ReadServiceInterface::getAddress
     */
    public function get($cartId);
}
