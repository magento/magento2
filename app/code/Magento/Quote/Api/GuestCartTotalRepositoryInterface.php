<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api;

/**
 * Cart totals repository interface for guest carts.
 * @api
 * @since 2.0.0
 */
interface GuestCartTotalRepositoryInterface
{
    /**
     * Return quote totals data for a specified cart.
     *
     * @param string $cartId The cart ID.
     * @return \Magento\Quote\Api\Data\TotalsInterface Quote totals data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @since 2.0.0
     */
    public function get($cartId);
}
