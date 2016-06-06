<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;

class OrderGet
{
    /** @var \Magento\GiftMessage\Api\OrderRepositoryInterface */
    protected $giftMessageOrderRepository;

    /** @var \Magento\GiftMessage\Api\OrderItemRepositoryInterface */
    protected $giftMessageOrderItemRepository;

    /** @var \Magento\Sales\Api\Data\OrderExtensionFactory */
    protected $orderExtensionFactory;

    /** @var \Magento\Sales\Api\Data\OrderItemExtensionFactory */
    protected $orderItemExtensionFactory;

    /**
     * Init plugin
     *
     * @param \Magento\GiftMessage\Api\OrderRepositoryInterface $giftMessageOrderRepository
     * @param \Magento\GiftMessage\Api\OrderItemRepositoryInterface $giftMessageOrderItemRepository
     * @param \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory
     * @param \Magento\Sales\Api\Data\OrderItemExtensionFactory $orderItemExtensionFactory
     */
    public function __construct(
        \Magento\GiftMessage\Api\OrderRepositoryInterface $giftMessageOrderRepository,
        \Magento\GiftMessage\Api\OrderItemRepositoryInterface $giftMessageOrderItemRepository,
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
        \Magento\Sales\Api\Data\OrderItemExtensionFactory $orderItemExtensionFactory
    ) {
        $this->giftMessageOrderRepository = $giftMessageOrderRepository;
        $this->giftMessageOrderItemRepository = $giftMessageOrderItemRepository;
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->orderItemExtensionFactory = $orderItemExtensionFactory;
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
        if ($extensionAttributes && $extensionAttributes->getGiftMessage()) {
            return $order;
        }

        try {
            /** @var \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage */
            $giftMessage = $this->giftMessageOrderRepository->get($order->getEntityId());
        } catch (NoSuchEntityException $e) {
            return $order;
        }

        /** @var \Magento\Sales\Api\Data\OrderExtension $orderExtension */
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        $orderExtension->setGiftMessage($giftMessage);
        $order->setExtensionAttributes($orderExtension);

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
            /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
            foreach ($orderItems as $orderItem) {
                $extensionAttributes = $orderItem->getExtensionAttributes();
                if ($extensionAttributes && $extensionAttributes->getGiftMessage()) {
                    continue;
                }

                try {
                    /* @var \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage */
                    $giftMessage = $this->giftMessageOrderItemRepository->get(
                        $order->getEntityId(),
                        $orderItem->getItemId()
                    );
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                /** @var \Magento\Sales\Api\Data\OrderItemExtension $orderItemExtension */
                $orderItemExtension = $extensionAttributes
                    ? $extensionAttributes
                    : $this->orderItemExtensionFactory->create();
                $orderItemExtension->setGiftMessage($giftMessage);
                $orderItem->setExtensionAttributes($orderItemExtension);
            }
        }
        return $order;
    }

    /**
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
