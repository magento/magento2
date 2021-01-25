<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Api;

use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Interface AddCartItemInterface
 * @api
 */
interface AddCartItemInterface
{
    /**
     * Add the specified cart item.
     *
     * @param CartItemInterface $cartItem The item.
     * @return CartItemInterface Item.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified item could not be saved to the cart.
     * @throws \Magento\Framework\Exception\InputException The specified item or cart is not valid.
     */
    public function execute(CartItemInterface $cartItem): CartItemInterface;
}
