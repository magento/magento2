<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Interface CartItemProcessorInterface
 * @since 2.0.0
 */
interface CartItemProcessorInterface
{
    /**
     * Convert cart item to buy request object
     *
     * @param CartItemInterface $cartItem
     * @return \Magento\Framework\DataObject|null
     * @since 2.0.0
     */
    public function convertToBuyRequest(CartItemInterface $cartItem);

    /**
     * Process cart item product/custom options
     *
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     * @since 2.0.0
     */
    public function processOptions(CartItemInterface $cartItem);
}
