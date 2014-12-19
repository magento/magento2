<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Api;

/**
 * @see \Magento\Checkout\Service\V1\Item\ReadServiceInterface
 * @see \Magento\Checkout\Service\V1\Item\WriteServiceInterface
 */
interface CartItemRepositoryInterface
{
    /**
     * Lists items that are assigned to a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Api\Data\CartItemInterface[] Array of items.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @see \Magento\Checkout\Service\V1\Item\ReadServiceInterface::getList
     */
    public function getList($cartId);

    /**
     * Adds the specified item to the specified cart.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\Checkout\Api\Data\CartItemInterface $cartItem The item.
     * @return \Magento\Checkout\Api\Data\CartItemInterface Item ID.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified item could not be saved to the cart.
     * @throws \Magento\Framework\Exception\InputException The specified item or cart is not valid.
     * @see \Magento\Checkout\Service\V1\Item\WriteServiceInterface::addItem
     * @see \Magento\Checkout\Service\V1\Item\WriteServiceInterface::updateItem
     */
    public function save(\Magento\Checkout\Api\Data\CartItemInterface $cartItem);

    /**
     * Remove bundle option
     *
     * @param \Magento\Checkout\Api\Data\CartItemInterface $cartItem
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Webapi\Exception
     */
    public function delete(\Magento\Checkout\Api\Data\CartItemInterface $cartItem);

    /**
     * Removes the specified item from the specified cart.
     *
     * @param int $cartId The cart ID.
     * @param int $itemId The item ID of the item to be removed.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified item or cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The item could not be removed.
     */
    public function deleteById($cartId, $itemId);
}
