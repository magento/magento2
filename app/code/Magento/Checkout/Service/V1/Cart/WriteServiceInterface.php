<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Cart;

/**
 * Cart write service interface.
 */
interface WriteServiceInterface
{
    /**
     * Enables an administrative or guest user to create an empty cart and quote for an anonymous customer.
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException The empty cart and quote could not be created.
     * @return int Cart ID.
     */
    public function create();

    /**
     * Assigns a specified customer to a specified shopping cart.
     *
     * @param int $cartId The cart ID.
     * @param int $customerId The customer ID.
     * @return boolean
     */
    public function assignCustomer($cartId, $customerId);

    /**
     * Places an order for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return int Order ID.
     */
    public function order($cartId);
}
