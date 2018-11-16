<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\GiftMessage\Api\OrderItemRepositoryInterface as GiftMessageItemRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;

class OrderItemGet
{
    /** @var OrderItemExtensionFactory */
    private $orderItemExtensionFactory;

    /** @var GiftMessageItemRepositoryInterface */
    private $orderItemRepository;

    /** @var OrderItemExtensionFactory */
    private $orderItemExtensionFactor;

    /**
     * @param GiftMessageItemRepositoryInterface $giftMessageOrderItemRepository
     * @param OrderItemExtensionFactory $orderItemExtensionFactor
     */
    public function __construct(
        GiftMessageItemRepositoryInterface $giftMessageOrderItemRepository,
        OrderItemExtensionFactory $orderItemExtensionFactor
    ) {
        $this->giftMessageOrderItemRepository = $giftMessageOrderItemRepository;
        $this->orderItemExtensionFactor = $orderItemExtensionFactor;
    }

    /**
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemInterface $result
     */
    public function afterGet(OrderItemRepositoryInterface $subject, OrderItemInterface $orderItem)
    {
        $extensionAttributes = $orderItem->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getGiftMessage()) {
            return $orderItem;
        }

        try {
            /* @var \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage */
            $giftMessage = $this->giftMessageOrderItemRepository->get(
                $orderItem->getOrderId(),
                $orderItem->getItemId()
            );
        } catch (NoSuchEntityException $e) {
            return $orderItem;
        }

        /** @var \Magento\Sales\Api\Data\OrderItemExtension $orderItemExtension */
        $orderItemExtension = $extensionAttributes
            ? $extensionAttributes
            : $this->orderItemExtensionFactory->create();
        $orderItemExtension->setGiftMessage($giftMessage);
        $orderItem->setExtensionAttributes($orderItemExtension);

        return $orderItem;
    }
}
