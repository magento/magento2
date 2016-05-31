<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Api;

/**
 * Interface OrderRepositoryInterface
 * @api
 */
interface OrderRepositoryInterface
{
    /**
     * Return the gift message for a specified order.
     *
     * @param int $orderId The order ID.
     * @return \Magento\GiftMessage\Api\Data\MessageInterface Gift message.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($orderId);

    /**
     * Set the gift message for an entire order.
     *
     * @param int $orderId The order ID.
     * @param \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage The gift message.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     */
    public function save($orderId, \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage);
}
