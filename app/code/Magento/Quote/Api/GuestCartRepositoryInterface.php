<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

/**
 * Cart Repository interface for guest carts.
 * @api
 */
interface GuestCartRepositoryInterface
{
    /**
     * Enable a guest user to return information for a specified cart.
     *
     * @param string $cartId
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($cartId);
}
