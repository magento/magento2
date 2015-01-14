<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Item;

/**
 * Write service interface.
 */
interface WriteServiceInterface
{
    /**
     * Adds the specified item to the specified cart.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Checkout\Service\V1\Data\Cart\Item $data The item.
     * @return int Item ID.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified item could not be saved to the cart.
     * @throws \Magento\Framework\Exception\InputException The specified item or cart is not valid.
     */
    public function addItem($cartId, \Magento\Checkout\Service\V1\Data\Cart\Item $data);

    /**
     * Updates the specified item in the specified cart.
     *
     * @param int $cartId The cart ID.
     * @param int $itemId The item ID of the item to be updated.
     * @param \Magento\Checkout\Service\V1\Data\Cart\Item $data The item.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified item or cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The item could not be updated.
     * @throws \Magento\Framework\Exception\InputException The specified item or cart is not valid.
     */
    public function updateItem($cartId, $itemId, \Magento\Checkout\Service\V1\Data\Cart\Item $data);

    /**
     * Removes the specified item from the specified cart.
     *
     * @param int $cartId The cart ID.
     * @param int $itemId The item ID of the item to be removed.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified item or cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The item could not be removed.
     */
    public function removeItem($cartId, $itemId);
}
