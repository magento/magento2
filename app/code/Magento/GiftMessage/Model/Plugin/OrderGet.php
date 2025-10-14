<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;

class OrderGet
{
    /**
     * @var \Magento\GiftMessage\Api\OrderRepositoryInterface
     */
    protected $giftMessageOrderRepository;

    /**
     * @var \Magento\GiftMessage\Api\OrderItemRepositoryInterface
     */
    protected $giftMessageOrderItemRepository;

    /**
     * Init plugin
     *
     * @param \Magento\GiftMessage\Api\OrderRepositoryInterface $giftMessageOrderRepository
     * @param \Magento\GiftMessage\Api\OrderItemRepositoryInterface $giftMessageOrderItemRepository
     */
    public function __construct(
        \Magento\GiftMessage\Api\OrderRepositoryInterface $giftMessageOrderRepository,
        \Magento\GiftMessage\Api\OrderItemRepositoryInterface $giftMessageOrderItemRepository
    ) {
        $this->giftMessageOrderRepository = $giftMessageOrderRepository;
        $this->giftMessageOrderItemRepository = $giftMessageOrderItemRepository;
    }

    /**
     * Get gift message
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $resultOrder
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $resultOrder
    ) {
        $resultOrder = $this->getOrderGiftMessage($resultOrder);
        $resultOrder = $this->getOrderItemGiftMessage($resultOrder);

        return $resultOrder;
    }

    /**
     * Get gift message for order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    protected function getOrderGiftMessage(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if (($extensionAttributes && $extensionAttributes->getGiftMessage()) || !$order->getGiftMessageId()) {
            return $order;
        }

        try {
            $giftMessage = $this->giftMessageOrderRepository->get($order->getEntityId());
        } catch (NoSuchEntityException $e) {
            return $order;
        }

        $extensionAttributes->setGiftMessage($giftMessage);
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    /**
     * Get gift message for items of order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    protected function getOrderItemGiftMessage(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $orderItems = $order->getItems();
        if (null !== $orderItems) {
            foreach ($orderItems as $orderItem) {
                $extensionAttributes = $orderItem->getExtensionAttributes();
                if (($extensionAttributes && $extensionAttributes->getGiftMessage()) ||
                    !$orderItem->getGiftMessageId()
                ) {
                    continue;
                }

                try {
                    $giftMessage = $this->giftMessageOrderItemRepository->get(
                        $order->getEntityId(),
                        $orderItem->getItemId()
                    );
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                $extensionAttributes->setGiftMessage($giftMessage);
                $orderItem->setExtensionAttributes($extensionAttributes);
            }
        }
        return $order;
    }

    /**
     * Add gift message details to orders
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $resultOrder
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Model\ResourceModel\Order\Collection $resultOrder
    ) {
        /** @var  $order */
        foreach ($resultOrder->getItems() as $order) {
            $this->afterGet($subject, $order);
        }
        return $resultOrder;
    }
}
