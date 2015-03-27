<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model\Plugin;

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
        /* @var \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage */
        $giftMessage = $this->getExtensionAttributes($order)->getGiftMessage();
        $this->giftMessageOrderRepository->save($order->getEntityId(), $giftMessage);
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
                /* @var \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage */
                $giftMessage = $this->getExtensionAttributes($orderItem)->getGiftMessage();
                $this->giftMessageOrderItemRepository->save(
                    $order->getEntityId(),
                    $orderItem->getItemId(),
                    $giftMessage
                );
            }
        }
        return $order;
    }

    /**
     * Wrap getExtensionAttributes
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface $entity
     * @return \Magento\Framework\Api\ExtensionAttributesInterface
     * @throws \LogicException
     */
    protected function getExtensionAttributes(\Magento\Framework\Api\ExtensibleDataInterface $entity)
    {
        /** @var \Magento\Framework\Api\ExtensionAttributesInterface|null $extensionAttributes */
        $extensionAttributes = $entity->getExtensionAttributes();
        if (!$extensionAttributes) {
            throw new \LogicException(
                'There are no extension attributes for ' . get_class($entity)
            );
        }
        return $extensionAttributes;
    }
}
