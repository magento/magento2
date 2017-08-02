<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Cart Management interface for guest carts.
 * @api
 * @since 2.0.0
 */
interface GuestCartManagementInterface
{
    /**
     * Enable an customer or guest user to create an empty cart and quote for an anonymous customer.
     *
     * @return string Cart ID.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The empty cart and quote could not be created.
     * @since 2.0.0
     */
    public function createEmptyCart();

    /**
     * Assign a specified customer to a specified shopping cart.
     *
     * @param string $cartId The cart ID.
     * @param int $customerId The customer ID.
     * @param int $storeId
     * @return boolean
     * @since 2.0.0
     */
    public function assignCustomer($cartId, $customerId, $storeId);

    /**
     * Place an order for a specified cart.
     *
     * @param string $cartId The cart ID.
     * @param PaymentInterface|null $paymentMethod
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return int Order ID.
     * @since 2.0.0
     */
    public function placeOrder($cartId, PaymentInterface $paymentMethod = null);
}
