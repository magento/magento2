<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GiftMessage\Api;

interface GiftMessageRepositoryInterface
{
    /**
     * Returns the gift message for a specified order.
     *
     * @param int $cartId The shopping cart ID.
     * @return \Magento\GiftMessage\Service\V1\Data\Message Gift message.
     * @see \Magento\GiftMessage\Service\V1\ReadServiceInterface::get
     */
    public function get($cartId);

    /**
     * Sets the gift message for an entire order.
     *
     * @param int $cartId The cart ID.
     * @param \Magento\GiftMessage\Service\V1\Data\Message $giftMessage The gift message.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\InputException You cannot add gift messages to empty carts.
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException You cannot add gift messages to
     * virtual products.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified gift message could not be saved.
     * @see \Magento\GiftMessage\Service\V1\WriteServiceInterface::setForQuote
     */
    public function save($cartId, \Magento\GiftMessage\Service\V1\Data\Message $giftMessage);
}
