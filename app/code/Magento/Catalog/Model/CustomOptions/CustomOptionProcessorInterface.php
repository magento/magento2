<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\CustomOptions;

use Magento\Quote\Api\Data\CartItemInterface;

interface CustomOptionProcessorInterface
{
    /**
     * Convert cart item to buy request object
     *
     * @param CartItemInterface $cartItem
     * @return \Magento\Framework\DataObject|null
     */
    public function convertToBuyRequest(CartItemInterface $cartItem);

    /**
     * Process cart item custom options
     *
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     */
    public function processCustomOptions(CartItemInterface $cartItem);
}
