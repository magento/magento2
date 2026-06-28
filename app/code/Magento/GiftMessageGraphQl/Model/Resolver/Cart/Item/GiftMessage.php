<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GiftMessageGraphQl\Model\Resolver\Cart\Item;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\ItemRepositoryInterface;
use Magento\GiftMessageGraphQl\Model\Config\Messages;

/**
 * Class provides ability to get GiftMessage for cart item
 */
class GiftMessage implements ResolverInterface
{
    /**
     * GiftMessage Constructor
     *
     * @param ItemRepositoryInterface $itemRepository
     * @param Messages $messagesConfig
     */
    public function __construct(
        private readonly ItemRepositoryInterface $itemRepository,
        private readonly Messages $messagesConfig
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
    ): ?array {
        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('"model" value must be specified'));
        }

        $quoteItem = $value['model'];

        if (!$this->messagesConfig->isMessagesAllowed('items', $quoteItem)
            || !$this->messagesConfig->isMessagesAllowed('item', $quoteItem)) {
            return null;
        }

        try {
            $giftItemMessage = $this->itemRepository->get($quoteItem->getQuoteId(), $quoteItem->getItemId());
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__('Can\'t load cart item'));
        }

        return $giftItemMessage ? [
            'to' => $giftItemMessage->getRecipient() ?? '',
            'from' => $giftItemMessage->getSender() ?? '',
            'message' => $giftItemMessage->getMessage() ?? ''
        ] : null;
    }
}
