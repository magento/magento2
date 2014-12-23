<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GiftMessage\Api;

interface GiftMessageItemRepositoryInterface
{
    /**
     * Returns the gift message for a specified item in a specified shopping cart.
     *
     * @param int $cartId The shopping cart ID.
     * @param int $itemId The item ID.
     * @return \Magento\GiftMessage\Service\V1\Data\Message Gift message.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified item does not exist in the cart.
     * @see \Magento\GiftMessage\Service\V1\ReadServiceInterface::getItemMessage
     */
    public function get($cartId, $itemId);

    /**
     * Sets the gift message for a specified item in a specified shopping cart.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\GiftMessage\Service\V1\Data\Message $giftMessage The gift message.
     * @param int $itemId The item ID.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\InputException You cannot add gift messages to empty carts.
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException You cannot add gift messages to
     * virtual products.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified gift message could not be saved.
     * @see \Magento\GiftMessage\Service\V1\WriteServiceInterface::setForItem
     */
    public function save($cartId, \Magento\GiftMessage\Service\V1\Data\Message $giftMessage, $itemId);
}
