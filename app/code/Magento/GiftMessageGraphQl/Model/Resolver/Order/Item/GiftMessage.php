<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GiftMessageGraphQl\Model\Resolver\Order\Item;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\OrderItemRepositoryInterface;
use Magento\GiftMessageGraphQl\Model\Config\Messages;
use Psr\Log\LoggerInterface;

/**
 * Class provides ability to get GiftMessage for order item
 */
class GiftMessage implements ResolverInterface
{
    /**
     * GiftMessage Constructor
     *
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param Messages $messagesConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly OrderItemRepositoryInterface $orderItemRepository,
        private readonly Messages $messagesConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('"model" value must be specified'));
        }

        $orderItem = $value['model'];

        if (!$this->messagesConfig->isMessagesAllowed('items', $orderItem) ||
            !$this->messagesConfig->isMessagesAllowed('item', $orderItem)) {
            return null;
        }

        try {
            $giftItemMessage = $this->orderItemRepository->get($orderItem->getOrderId(), $orderItem->getItemId());
        } catch (LocalizedException $e) {
            $this->logger->error('Can\'t load message for order item', [
                'order_id' => $orderItem->getOrderId(),
                'item_id' => $orderItem->getItemId(),
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        return $giftItemMessage ? [
            'to' => $giftItemMessage->getRecipient() ?? '',
            'from' => $giftItemMessage->getSender() ?? '',
            'message' => $giftItemMessage->getMessage() ?? '',
        ] : null;
    }
}
