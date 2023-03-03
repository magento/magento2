<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Api;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface OrderItemRepositoryInterface
 * @api
 * @since 100.0.2
 */
interface OrderItemRepositoryInterface
{
    /**
     * Return the gift message for a specified item in a specified order.
     *
     * @param OrderInterface $order The order.
     * @param int $orderItemId The item ID.
     * @return \Magento\GiftMessage\Api\Data\MessageInterface Gift message.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByOrder(OrderInterface $order, int $orderItemId);

    /**
     * Return the gift message for a specified item in a specified order.
     *
     * @param int $orderId The order ID.
     * @param int $orderItemId The item ID.
     * @return \Magento\GiftMessage\Api\Data\MessageInterface Gift message.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($orderId, $orderItemId);

    /**
     * Set the gift message for a specified item in a specified order.
     *
     * @param OrderInterface $order The order.
     * @param int $orderItemId The item ID.
     * @param \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage The gift message.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveForOrder(
        OrderInterface $order,
        int $orderItemId,
        \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage
    );

    /**
     * Set the gift message for a specified item in a specified order.
     *
     * @param int $orderId The order ID.
     * @param int $orderItemId The item ID.
     * @param \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage The gift message.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save($orderId, $orderItemId, \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage);
}
