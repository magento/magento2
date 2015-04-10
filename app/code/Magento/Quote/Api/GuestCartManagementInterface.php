<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

interface GuestCartManagementInterface
{
    /**
     * Enables an guest user to create an empty cart and quote for an anonymous customer.
     *
     * @return int Cart ID.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The empty cart and quote could not be created.
     */
    public function createEmptyCart();

    /**
     * Places an order for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return int Order ID.
     */
    public function placeOrder($cartId);
}
