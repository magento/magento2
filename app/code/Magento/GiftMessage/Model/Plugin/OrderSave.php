<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model\Plugin;

use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class \Magento\GiftMessage\Model\Plugin\OrderSave
 *
 */
class OrderSave
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
     * Save gift message
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $resultOrder
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws CouldNotSaveException
     * @since 2.0.11
     */
    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $resultOrder
    ) {
        /** @var \Magento\Sales\Api\Data\OrderInterface $resultOrder */
        $resultOrder = $this->saveOrderGiftMessage($resultOrder);
        $resultOrder = $this->saveOrderItemGiftMessage($resultOrder);

        return $resultOrder;
    }

    /**
     * Save gift message for order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws CouldNotSaveException
     */
    protected function saveOrderGiftMessage(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if (null !== $extensionAttributes &&
            null !== $extensionAttributes->getGiftMessage()
        ) {
            /* @var \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage */
            $giftMessage = $extensionAttributes->getGiftMessage();
            try {
                $this->giftMessageOrderRepository->save($order->getEntityId(), $giftMessage);
            } catch (\Exception $e) {
                throw new CouldNotSaveException(
                    __('Could not add gift message to order: "%1"', $e->getMessage()),
                    $e
                );
            }
        }
        return $order;
    }

    /**
     * Save gift message for items of order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws CouldNotSaveException
     */
    protected function saveOrderItemGiftMessage(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $items = $order->getItems();
        if (null !== $items) {
            /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
            foreach ($items as $orderItem) {
                $extensionAttribute = $orderItem->getExtensionAttributes();
                if (null !== $extensionAttribute &&
                    null !== $extensionAttribute->getGiftMessage()
                ) {
                    /* @var \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage */
                    $giftMessage = $extensionAttribute->getGiftMessage();
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
