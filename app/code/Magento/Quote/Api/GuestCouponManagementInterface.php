<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api;

/**
 * Coupon management interface for guest carts.
 * @api
 * @since 2.0.0
 */
interface GuestCouponManagementInterface
{
    /**
     * Return information for a coupon in a specified cart.
     *
     * @param string $cartId The cart ID.
     * @return string The coupon code data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @since 2.0.0
     */
    public function get($cartId);

    /**
     * Add a coupon by code to a specified cart.
     *
     * @param string $cartId The cart ID.
     * @param string $couponCode The coupon code data.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified coupon could not be added.
     * @since 2.0.0
     */
    public function set($cartId, $couponCode);

    /**
     * Delete a coupon from a specified cart.
     *
     * @param string $cartId The cart ID.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotDeleteException The specified coupon could not be deleted.
     * @since 2.0.0
     */
    public function remove($cartId);
}
