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
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\OrderItemRepositoryInterface;
use Magento\GiftMessage\Helper\Message as GiftMessageHelper;
use Magento\GiftMessageGraphQl\Model\Config\Messages;

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
     */
    public function __construct(
        private readonly OrderItemRepositoryInterface $orderItemRepository,
        private readonly Messages $messagesConfig
    ) {
    }

    /**
     * Return information about Gift message for order item
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array|Value|mixed
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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

        if (!$this->messagesConfig->isMessagesAllowed('items', $orderItem)) {
            return null;
        }

        if (!$this->messagesConfig->isMessagesAllowed('item', $orderItem)) {
            return null;
        }

        try {
            $giftItemMessage = $this->orderItemRepository->get($orderItem->getOrderId(), $orderItem->getItemId());
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__('Can\'t load message for order item'));
        }

        if ($giftItemMessage === null) {
            return null;
        }

        return [
            'to' => $giftItemMessage->getRecipient() ?? '',
            'from' =>  $giftItemMessage->getSender() ?? '',
            'message'=>  $giftItemMessage->getMessage() ?? ''
        ];
    }
}
