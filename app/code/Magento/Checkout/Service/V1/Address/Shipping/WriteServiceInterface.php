<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Address\Shipping;

/** Quote shipping address write service interface. */
interface WriteServiceInterface
{
    /**
     * Assigns a specified shipping address to a specified cart.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Checkout\Service\V1\Data\Cart\Address $addressData The shipping address data.
     * @return int Address ID.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\InputException The specified cart ID or address data is not valid.
     */
    public function setAddress($cartId, $addressData);
}
