<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Api;

/**
 * Cart Repository interface for guest carts.
 * @api
 * @since 100.0.2
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
