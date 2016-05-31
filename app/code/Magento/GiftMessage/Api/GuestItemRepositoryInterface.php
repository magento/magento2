<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Api;

/**
 * Interface GuestItemRepositoryInterface
 * @api
 */
interface GuestItemRepositoryInterface
{
    /**
     * Return the gift message for a specified item in a specified shopping cart.
     *
     * @param string $cartId The shopping cart ID.
     * @param int $itemId The item ID.
     * @return \Magento\GiftMessage\Api\Data\MessageInterface Gift message.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified item does not exist in the cart.
     */
    public function get($cartId, $itemId);

    /**
     * Set the gift message for a specified item in a specified shopping cart.
     *
     * @param string $cartId The cart ID.
     * @param \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage The gift message.
     * @param int $itemId The item ID.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\InputException You cannot add gift messages to empty carts.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified gift message could not be saved.
     */
    public function save($cartId, \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage, $itemId);
}
