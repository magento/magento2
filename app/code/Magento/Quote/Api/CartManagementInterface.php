<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Interface CartManagementInterface
 * @api
 * @since 2.0.0
 */
interface CartManagementInterface
{
    /**
     * Checkout types: Checkout as Guest
     */
    const METHOD_GUEST = 'guest';

    /**
     * Creates an empty cart and quote for a guest.
     *
     * @return int Cart ID.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The empty cart and quote could not be created.
     * @since 2.0.0
     */
    public function createEmptyCart();

    /**
     * Creates an empty cart and quote for a specified customer if customer does not have a cart yet.
     *
     * @param int $customerId The customer ID.
     * @return int new cart ID if customer did not have a cart or ID of the existing cart otherwise.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The empty cart and quote could not be created.
     * @since 2.0.0
     */
    public function createEmptyCartForCustomer($customerId);

    /**
     * Returns information for the cart for a specified customer.
     *
     * @param int $customerId The customer ID.
     * @return \Magento\Quote\Api\Data\CartInterface Cart object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified customer does not exist.
     * @since 2.0.0
     */
    public function getCartForCustomer($customerId);

    /**
     * Assigns a specified customer to a specified shopping cart.
     *
     * @param int $cartId The cart ID.
     * @param int $customerId The customer ID.
     * @param int $storeId
     * @return boolean
     * @since 2.0.0
     */
    public function assignCustomer($cartId, $customerId, $storeId);

    /**
     * Places an order for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @param PaymentInterface|null $paymentMethod
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return int Order ID.
     * @since 2.0.0
     */
    public function placeOrder($cartId, PaymentInterface $paymentMethod = null);
}
