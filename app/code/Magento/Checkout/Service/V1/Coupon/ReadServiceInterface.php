<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Coupon;

/**
 * Coupon read service interface.
 */
interface ReadServiceInterface
{
    /**
     * Returns information for a coupon in a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart\Coupon Coupon object.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function get($cartId);
}
