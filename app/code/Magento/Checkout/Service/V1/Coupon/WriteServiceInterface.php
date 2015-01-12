<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Coupon;

/**
 * Coupon write service interface.
 */
interface WriteServiceInterface
{
    /**
     * Adds a coupon by code to a specified cart.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Checkout\Service\V1\Data\Cart\Coupon $couponCodeData The coupon code data.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified coupon could not be added.
     */
    public function set($cartId, \Magento\Checkout\Service\V1\Data\Cart\Coupon $couponCodeData);

    /**
     * Deletes a coupon from a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotDeleteException The specified coupon could not be deleted.
     */
    public function delete($cartId);
}
