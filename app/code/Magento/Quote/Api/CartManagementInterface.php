<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

interface CartManagementInterface
{
    /**
     * Creates an empty cart and quote for a guest.
     *
     * @return int Cart ID.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The empty cart and quote could not be created.
     */
    public function createEmptyCart();

    /**
     * Creates an empty cart and quote for a specified customer.
     *
     * @param int $customerId The customer ID.
     * @return int Cart ID.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The empty cart and quote could not be created.
     */
    public function createEmptyCartForCustomer($customerId);

    /**
     * Returns information for the cart for a specified customer.
     *
     * @param int $customerId The customer ID.
     * @return \Magento\Quote\Api\Data\CartInterface Cart object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified customer does not exist.
     */
    public function getCartForCustomer($customerId);

    /**
     * Assigns a specified customer to a specified shopping cart.
     *
     * @param int $cartId The cart ID.
     * @param int $customerId The customer ID.
     * @param int $storeId
     * @return boolean
     */
    public function assignCustomer($cartId, $customerId, $storeId);

    /**
     * Places an order for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @param int[]|null $agreements
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return int Order ID.
     */
    public function placeOrder($cartId, $agreements = null);

    /**
     * Registers a customer and places an order for the specified cart.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $password
     * @param int[]|null $agreements
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return int Order ID.
     */
    public function placeOrderCreatingAccount($cartId, $customer, $password, $agreements = null);
}
