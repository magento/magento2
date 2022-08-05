<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Model\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GiftMessage\Api\OrderItemRepositoryInterface as GiftMessageItemRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * Plugin for adding gift message to order item
 */
class OrderItemGet
{

    /**
     * @var OrderItemExtensionFactory
     */
    private $orderItemExtensionFactory;

    /**
     * @var GiftMessageItemRepositoryInterface
     */
    private $giftMessageItemRepository;

    /**
     * OrderItemGet constructor.
     *
     * @param GiftMessageItemRepositoryInterface $giftMessageItemRepository
     * @param OrderItemExtensionFactory $orderItemExtensionFactory
     */
    public function __construct(
        GiftMessageItemRepositoryInterface $giftMessageItemRepository,
        OrderItemExtensionFactory $orderItemExtensionFactory
    ) {
        $this->giftMessageItemRepository = $giftMessageItemRepository;
        $this->orderItemExtensionFactory = $orderItemExtensionFactory;
    }

    /**
     * Add gift message for order item
     *
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemInterface $orderItem
     * @return OrderItemInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(OrderItemRepositoryInterface $subject, OrderItemInterface $orderItem)
    {
        $extensionAttributes = $orderItem->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getGiftMessage()) {
            return $orderItem;
        }
        try {
            /* @var \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage */
            $giftMessage = $this->giftMessageItemRepository->get(
                $orderItem->getOrderId(),
                $orderItem->getItemId()
            );
        } catch (NoSuchEntityException $e) {
            return $orderItem;
        }
        /** @var \Magento\Sales\Api\Data\OrderItemExtension $orderItemExtension */
        $orderItemExtension = $extensionAttributes ?: $this->orderItemExtensionFactory->create();
        $orderItemExtension->setGiftMessage($giftMessage);
        $orderItem->setExtensionAttributes($orderItemExtension);

        return $orderItem;
    }
}
