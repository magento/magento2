<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Api;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface OrderRepositoryInterface
 * @api
 * @since 100.0.2
 */
interface OrderRepositoryInterface
{
    /**
     * Return the gift message for a specified order.
     *
     * @param OrderInterface $order The order.
     * @return \Magento\GiftMessage\Api\Data\MessageInterface Gift message.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByOrder(OrderInterface $order);

    /**
     * Return the gift message for a specified order ID
     *
     * @param int $orderId The order ID.
     * @return \Magento\GiftMessage\Api\Data\MessageInterface Gift message.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($orderId);

    /**
     * Set the gift message for an entire order.
     *
     * @param OrderInterface $order The order.
     * @param \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage The gift message.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     */
    public function saveForOrder(OrderInterface $order, \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage);

    /**
     * Set the gift message for an entire order loaded from ID
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
