<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Api;

interface CartTotalRepositoryInterface
{
    /**
     * Returns quote totals data for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Api\Data\TotalsItemInterface Quote totals data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @see \Magento\Checkout\Service\V1\Cart\TotalsServiceInterface::getTotals
     */
    public function get($cartId);
}
