<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Interface AddCartItemInterface
 * @api
  */
interface UpdateCartItemInterface
{
    /**
     * Update the specified cart item.
     *
     * @param CartItemInterface $cartItem The item.
     * @return CartItemInterface Item.
     * @throws NoSuchEntityException The specified cart does not exist.
     * @throws CouldNotSaveException The specified item could not be saved to the cart.
     * @throws InputException The specified item or cart is not valid.
     */
    public function execute(CartItemInterface $cartItem);
}
