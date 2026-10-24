<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Quote\Api;

/**
 * Cart totals repository interface for guest carts.
 * @api
 * @since 100.0.2
 */
interface GuestCartTotalRepositoryInterface
{
    /**
     * Return quote totals data for a specified cart.
     *
     * @param string $cartId The cart ID.
     * @return \Magento\Quote\Api\Data\TotalsInterface Quote totals data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function get($cartId);
}
