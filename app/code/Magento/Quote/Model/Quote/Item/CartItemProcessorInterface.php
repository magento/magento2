<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Interface CartItemProcessorInterface
 */
interface CartItemProcessorInterface
{
    /**
     * Convert cart item to buy request object
     *
     * @param CartItemInterface $cartItem
     * @return \Magento\Framework\DataObject|null
     */
    public function convertToBuyRequest(CartItemInterface $cartItem);

    /**
     * Process cart item product/custom options
     *
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     */
    public function processOptions(CartItemInterface $cartItem);
}
