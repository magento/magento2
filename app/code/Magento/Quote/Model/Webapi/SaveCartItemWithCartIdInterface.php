<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Webapi;

/**
 * Interface SaveCartItemWithCartIdInterface
 */
interface SaveCartItemWithCartIdInterface
{
    /**
     * Add/update the specified cart item for given cartId.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Quote\Api\Data\CartItemInterface $cartItem The item.
     * @return \Magento\Quote\Api\Data\CartItemInterface Item.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified item could not be saved to the cart.
     * @throws \Magento\Framework\Exception\InputException The specified item or cart is not valid.
     */
    public function saveForCart($cartId, \Magento\Quote\Api\Data\CartItemInterface $cartItem);
}
