<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Cart;

/**
 * Totals service interface.
 */
interface TotalsServiceInterface
{
    /**
     * Returns quote totals data for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart\Totals Quote totals data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getTotals($cartId);
}
