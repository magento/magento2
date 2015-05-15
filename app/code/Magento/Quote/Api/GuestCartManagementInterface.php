<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

/**
 * Cart Management interface for guest carts.
 * @api
 */
interface GuestCartManagementInterface
{
    /**
     * Enables an customer or guest user to create an empty cart and quote for an anonymous customer.
     *
     * @return string Cart ID.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The empty cart and quote could not be created.
     */
    public function createEmptyCart();

    /**
     * Assigns a specified customer to a specified shopping cart.
     *
     * @param string $cartId The cart ID.
     * @param int $customerId The customer ID.
     * @param int $storeId
     * @return boolean
     */
    public function assignCustomer($cartId, $customerId, $storeId);

    /**
     * Places an order for a specified cart.
     *
     * @param string $cartId The cart ID.
     * @return int Order ID.
     */
    public function placeOrder($cartId);
}
