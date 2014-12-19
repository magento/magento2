<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Api;

interface CartManagementInterface
{
    /**
     * Enables an administrative or guest user to create an empty cart and quote for an anonymous customer.
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException The empty cart and quote could not be created.
     * @return int Cart ID.
     * @see \Magento\Checkout\Service\V1\Cart\WriteServiceInterface::create
     */
    public function createEmptyCart();

    /**
     * Returns information for the cart for a specified customer.
     *
     * @param int $customerId The customer ID.
     * @return \Magento\Checkout\Api\Data\CartInterface Cart object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified customer does not exist.
     * @see \Magento\Checkout\Service\V1\Cart\ReadServiceInterface::getCartForCustomer
     */
    public function getCartForCustomer($customerId);

    /**
     * Assigns a specified customer to a specified shopping cart.
     *
     * @param int $cartId The cart ID.
     * @param int $customerId The customer ID.
     * @return boolean
     * @see \Magento\Checkout\Service\V1\Cart\WriteServiceInterface::assignCustomer
     */
    public function assignCustomer($cartId, $customerId);

    /**
     * Returns information for a coupon in a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return string The coupon code data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @see \Magento\Checkout\Service\V1\Coupon\ReadServiceInterface::get
     */
    public function getCouponCode($cartId);

    /**
     * Adds a coupon by code to a specified cart.
     *
     * @param int $cartId The cart ID.
     * @param string $couponCode The coupon code data.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified coupon could not be added.
     * @see \Magento\Checkout\Service\V1\Coupon\WriteServiceInterface::set
     */
    public function setCoupon($cartId, $couponCode);

    /**
     * Deletes a coupon from a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotDeleteException The specified coupon could not be deleted.
     * @see \Magento\Checkout\Service\V1\Coupon\WriteServiceInterface::delete
     */
    public function removeCoupon($cartId);

    /**
     * Places an order for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return int Order ID.
     * @see \Magento\Checkout\Service\V1\Cart\WriteServiceInterface::order
     */
    public function order($cartId);
}
