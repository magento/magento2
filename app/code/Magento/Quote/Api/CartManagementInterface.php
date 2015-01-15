<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

interface CartManagementInterface
{
    /**
     * Enables an administrative or guest user to create an empty cart and quote for an anonymous customer.
     *
     * @param int $storeId
     * @throws \Magento\Framework\Exception\CouldNotSaveException The empty cart and quote could not be created.
     * @return int Cart ID.
     * @see \Magento\Checkout\Service\V1\Cart\WriteServiceInterface::create
     */
    public function createEmptyCart($storeId);

    /**
     * Returns information for the cart for a specified customer.
     *
     * @param int $customerId The customer ID.
     * @return \Magento\Quote\Api\Data\CartInterface Cart object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified customer does not exist.
     * @see \Magento\Checkout\Service\V1\Cart\ReadServiceInterface::getCartForCustomer
     */
    public function getCartForCustomer($customerId);

    /**
     * Assigns a specified customer to a specified shopping cart.
     *
     * @param int $cartId The cart ID.
     * @param int $customerId The customer ID.
     * @param int $storeId
     * @return boolean
     * @see \Magento\Checkout\Service\V1\Cart\WriteServiceInterface::assignCustomer
     */
    public function assignCustomer($cartId, $customerId, $storeId);

    /**
     * Places an order for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return int Order ID.
     * @see \Magento\Checkout\Service\V1\Cart\WriteServiceInterface::order
     */
    public function order($cartId);
}
