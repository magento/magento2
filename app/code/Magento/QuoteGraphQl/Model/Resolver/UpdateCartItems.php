<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\UpdateCartItem;

/**
 * @inheritdoc
 */
class UpdateCartItems implements ResolverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var UpdateCartItem
     */
    private $updateCartItem;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var CartItemRepositoryInterface
     */
    private $cartItemRepository;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param UpdateCartItem $updateCartItem
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartItemRepositoryInterface $cartItemRepository,
        UpdateCartItem $updateCartItem,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->cartItemRepository = $cartItemRepository;
        $this->updateCartItem = $updateCartItem;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['input']['cart_id']) || empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing.'));
        }
        $maskedCartId = $args['input']['cart_id'];

        if (!isset($args['input']['cart_items']) || empty($args['input']['cart_items'])
            || !is_array($args['input']['cart_items'])
        ) {
            throw new GraphQlInputException(__('Required parameter "cart_items" is missing.'));
        }
        $cartItems = $args['input']['cart_items'];

        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());

        try {
            $this->processCartItems($cart, $cartItems);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }

    /**
     * Process cart items
     *
     * @param Quote $cart
     * @param array $items
     * @throws GraphQlInputException
     * @throws LocalizedException
     */
    private function processCartItems(Quote $cart, array $items): void
    {
        foreach ($items as $item) {
            if (!isset($item['cart_item_id']) || empty($item['cart_item_id'])) {
                throw new GraphQlInputException(__('Required parameter "cart_item_id" for "cart_items" is missing.'));
            }
            $itemId = (int)$item['cart_item_id'];

            if (!isset($item['quantity'])) {
                throw new GraphQlInputException(__('Required parameter "quantity" for "cart_items" is missing.'));
            }
            $qty = (float)$item['quantity'];

            if ($qty <= 0.0) {
                $this->cartItemRepository->deleteById((int)$cart->getId(), $itemId);
            } else {
                $customizableOptions = $item['customizable_options'] ?? null;

                if ($customizableOptions === null) { // Update only item's qty
                    $this->updateItemQty($itemId, $cart, $qty);
                } else { // Update customizable options (and QTY if changed)
                    $this->updateCartItem->execute($cart, $itemId, $qty, $customizableOptions);
                    $this->quoteRepository->save($cart);
                }
            }
        }
    }

    /**
     * Updates item qty for the specified cart
     *
     * @param int $itemId
     * @param Quote $cart
     * @param float $qty
     * @throws GraphQlNoSuchEntityException
     * @throws NoSuchEntityException
     * @throws GraphQlNoSuchEntityException
     */
    private function updateItemQty(int $itemId, Quote $cart, float $qty)
    {
        $cartItem = $cart->getItemById($itemId);
        if ($cartItem === false) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find cart item with id: %1.', $itemId)
            );
        }
        $cartItem->setQty($qty);
        $this->cartItemRepository->save($cartItem);
    }
}
