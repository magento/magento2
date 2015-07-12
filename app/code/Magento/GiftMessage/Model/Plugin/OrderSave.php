<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model\Plugin;

use Magento\Framework\Exception\CouldNotSaveException;

class OrderSave
{
    /** @var \Magento\GiftMessage\Api\OrderRepositoryInterface */
    protected $giftMessageOrderRepository;

    /** @var \Magento\GiftMessage\Api\OrderItemRepositoryInterface */
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
     * Save gift message
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param callable $proceed
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        /** @var \Magento\Sales\Api\Data\OrderInterface $resultOrder */
        $resultOrder = $proceed($order);
        $resultOrder = $this->saveOrderGiftMessage($resultOrder);
        $resultOrder = $this->saveOrderItemGiftMessage($resultOrder);

        return $resultOrder;
    }

    /**
     * Save gift message for order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    protected function saveOrderGiftMessage(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        if (
            null !== $order->getExtensionAttributes() &&
            null !== $order->getExtensionAttributes()->getGiftMessage()
        ) {
            /* @var \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage */
            $giftMessage = $order->getExtensionAttributes()->getGiftMessage();
            if (null !== $giftMessage) {
                try {
                    $this->giftMessageOrderRepository->save($order->getEntityId(), $giftMessage);
                } catch (\Exception $e) {
                    throw new CouldNotSaveException(
                        __('Could not add gift message to order: "%1"', $e->getMessage()),
                        $e
                    );
                }
            }
        }
        return $order;
    }

    /**
     * Save gift message for items of order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    protected function saveOrderItemGiftMessage(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        if (null !== $order->getItems()) {
            /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
            foreach ($order->getItems() as $orderItem) {
                if (
                    null !== $orderItem->getExtensionAttributes() &&
                    null !== $orderItem->getExtensionAttributes()->getGiftMessage()
                ) {
                    /* @var \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage */
                    $giftMessage = $orderItem->getExtensionAttributes()->getGiftMessage();
                    try {
                        $this->giftMessageOrderItemRepository->save(
                            $order->getEntityId(),
                            $orderItem->getItemId(),
                            $giftMessage
                        );
                    } catch (\Exception $e) {
                        throw new CouldNotSaveException(
                            __('Could not add gift message to order\'s item: "%1"', $e->getMessage()),
                            $e
                        );
                    }
                }
            }
        }
        return $order;
    }
}
