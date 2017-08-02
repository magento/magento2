<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

/**
 * Interface CartItemRepositoryInterface
 * @api
 * @since 2.0.0
 */
interface CartItemRepositoryInterface
{
    /**
     * Lists items that are assigned to a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Quote\Api\Data\CartItemInterface[] Array of items.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @since 2.0.0
     */
    public function getList($cartId);

    /**
     * Add/update the specified cart item.
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $cartItem The item.
     * @return \Magento\Quote\Api\Data\CartItemInterface Item.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified item could not be saved to the cart.
     * @throws \Magento\Framework\Exception\InputException The specified item or cart is not valid.
     * @since 2.0.0
     */
    public function save(\Magento\Quote\Api\Data\CartItemInterface $cartItem);

    /**
     * Removes the specified item from the specified cart.
     *
     * @param int $cartId The cart ID.
     * @param int $itemId The item ID of the item to be removed.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified item or cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The item could not be removed.
     * @since 2.0.0
     */
    public function deleteById($cartId, $itemId);
}
