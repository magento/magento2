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
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * @inheritdoc
 */
class UpdateCartItems implements ResolverInterface
{
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
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartItemRepositoryInterface $cartItemRepository
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->cartItemRepository = $cartItemRepository;
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
            $itemId = $item['cart_item_id'];

            if (!isset($item['quantity'])) {
                throw new GraphQlInputException(__('Required parameter "quantity" for "cart_items" is missing.'));
            }
            $quantity = (float)$item['quantity'];

            $cartItem = $cart->getItemById($itemId);
            if ($cartItem === false) {
                throw new GraphQlNoSuchEntityException(
                    __('Could not find cart item with id: %1.', $item['cart_item_id'])
                );
            }

            if ($quantity <= 0.0) {
                $this->cartItemRepository->deleteById((int)$cart->getId(), $itemId);
            } else {
                $cartItem->setQty($quantity);
                $this->validateCartItem($cartItem);
                $this->cartItemRepository->save($cartItem);
            }
        }
    }

    /**
     * Validate cart item
     *
     * @param Item $cartItem
     * @return void
     * @throws GraphQlInputException
     */
    private function validateCartItem(Item $cartItem): void
    {
        if ($cartItem->getHasError()) {
            $errors = [];
            foreach ($cartItem->getMessage(false) as $message) {
                $errors[] = $message;
            }

            if (!empty($errors)) {
                throw new GraphQlInputException(
                    __(
                        'Could not update the product with SKU %sku: %message',
                        ['sku' => $cartItem->getSku(), 'message' => __(implode("\n", $errors))]
                    )
                );
            }
        }
    }
}
