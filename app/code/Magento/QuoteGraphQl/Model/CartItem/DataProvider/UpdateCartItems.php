<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Api\Data\MessageInterfaceFactory;
use Magento\GiftMessage\Api\ItemRepositoryInterface;
use Magento\GiftMessageGraphQl\Model\Config\Messages;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\UpdateCartItem;

/**
 * Class contain update cart items methods
 */
class UpdateCartItems
{
    /**
     * UpdateCartItems Constructor
     *
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param UpdateCartItem $updateCartItem
     * @param ItemRepositoryInterface $itemRepository
     * @param Messages $messagesConfig
     * @param MessageInterfaceFactory $giftMessageFactory
     */
    public function __construct(
        private readonly CartItemRepositoryInterface $cartItemRepository,
        private readonly UpdateCartItem $updateCartItem,
        private readonly ItemRepositoryInterface $itemRepository,
        private readonly Messages $messagesConfig,
        private readonly MessageInterfaceFactory $giftMessageFactory
    ) {
    }

    /**
     * Process cart items
     *
     * @param Quote $cart
     * @param array $items
     *
     * @throws GraphQlInputException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processCartItems(Quote $cart, array $items): void
    {
        foreach ($items as $item) {
            if (empty($item['cart_item_id'])) {
                throw new GraphQlInputException(__('Required parameter "cart_item_id" for "cart_items" is missing.'));
            }

            $itemId = (int)$item['cart_item_id'];
            $customizableOptions = $item['customizable_options'] ?? [];
            $cartItem = $cart->getItemById($itemId);

            if ($cartItem && $cartItem->getParentItemId()) {
                throw new GraphQlInputException(__('Child items may not be updated.'));
            }

            if (count($customizableOptions) === 0 && !isset($item['quantity'])) {
                throw new GraphQlInputException(__('Required parameter "quantity" for "cart_items" is missing.'));
            }

            $quantity = (float)$item['quantity'];

            if ($quantity <= 0.0) {
                $this->cartItemRepository->deleteById((int)$cart->getId(), $itemId);
            } else {
                $this->updateCartItem->execute($cart, $itemId, $quantity, $customizableOptions);
            }

            if (!empty($item['gift_message'])) {
                try {
                    if (!$this->messagesConfig->isMessagesAllowed('items', $cartItem)) {
                        continue;
                    }
                    if (!$this->messagesConfig->isMessagesAllowed('item', $cartItem)) {
                        continue;
                    }

                    /** @var  MessageInterface $giftItemMessage */
                    $giftItemMessage = $this->itemRepository->get($cart->getEntityId(), $itemId);

                    if (!$giftItemMessage) {
                        /** @var  MessageInterface $giftMessage */
                        $giftMessage = $this->giftMessageFactory->create();
                        $this->updateGiftMessageForItem($cart, $giftMessage, $item, $itemId);
                        continue;
                    }
                } catch (LocalizedException $exception) {
                    throw new GraphQlInputException(__('Gift Message cannot be updated.'));
                }

                $this->updateGiftMessageForItem($cart, $giftItemMessage, $item, $itemId);
            }
        }
    }

    /**
     * Update Gift Message for Quote item
     *
     * @param Quote               $cart
     * @param MessageInterface    $giftItemMessage
     * @param array               $item
     * @param int                 $itemId
     *
     * @throws GraphQlInputException
     */
    private function updateGiftMessageForItem(Quote $cart, MessageInterface $giftItemMessage, array $item, int $itemId)
    {
        try {
            $giftItemMessage->setRecipient($item['gift_message']['to']);
            $giftItemMessage->setSender($item['gift_message']['from']);
            $giftItemMessage->setMessage($item['gift_message']['message']);
            $this->itemRepository->save($cart->getEntityId(), $giftItemMessage, $itemId);
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__('Gift Message cannot be updated'));
        }
    }
}
