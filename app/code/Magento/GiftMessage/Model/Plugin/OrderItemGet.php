<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\GiftMessage\Api\OrderItemRepositoryInterface as GiftMessageItemRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;

/**
 * Plugin for adding gift message to order item
 */
class OrderItemGet
{
    /** @var OrderItemExtensionFactory */
    private $orderItemFactory;

    /**@var GiftMessageItemRepositoryInterface */
    private $giftMessageRepository;

    /**
     * @param GiftMessageItemRepositoryInterface $giftMessageOrderItemRepository
     * @param OrderItemExtensionFactory $orderItemExtensionFactor
     */
    public function __construct(
        GiftMessageItemRepositoryInterface $giftMessageRepository,
        OrderItemExtensionFactory $orderItemFactory
    ) {
        $this->giftMessageRepository = $giftMessageRepository;
        $this->orderItemFactory = $orderItemFactory;
    }

    /**
     * Add gift message for order item
     *
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemInterface $orderItem
     * @return OrderItemInterface
     */
    public function afterGet(OrderItemRepositoryInterface $subject, OrderItemInterface $orderItem)
    {
        $extensionAttributes = $orderItem->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getGiftMessage()) {
            return $orderItem;
        }

        try {
            /* @var \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage */
            $giftMessage = $this->giftMessageRepository->get(
                $orderItem->getOrderId(),
                $orderItem->getItemId()
            );
        } catch (NoSuchEntityException $e) {
            return $orderItem;
        }

        /** @var \Magento\Sales\Api\Data\OrderItemExtension $orderItemExtension */
        $orderItemExtension = $extensionAttributes ?: $this->orderItemFactory->create();
        $orderItemExtension->setGiftMessage($giftMessage);
        $orderItem->setExtensionAttributes($orderItemExtension);

        return $orderItem;
    }
}
