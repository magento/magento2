<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

use Magento\Quote\Api\Data\CartItemInterface;

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
     * Process cart item product options
     *
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     */
    public function processProductOptions(CartItemInterface $cartItem);
}
