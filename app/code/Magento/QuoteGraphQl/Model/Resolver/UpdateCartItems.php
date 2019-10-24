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
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param UpdateCartItem $updateCartItem
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartItemRepositoryInterface $cartItemRepository,
        UpdateCartItem $updateCartItem,
        CartRepositoryInterface $cartRepository
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->cartItemRepository = $cartItemRepository;
        $this->updateCartItem = $updateCartItem;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing.'));
        }
        $maskedCartId = $args['input']['cart_id'];

        if (empty($args['input']['cart_items'])
            || !is_array($args['input']['cart_items'])
        ) {
            throw new GraphQlInputException(__('Required parameter "cart_items" is missing.'));
        }
        $cartItems = $args['input']['cart_items'];

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        try {
            $this->processCartItems($cart, $cartItems);
            $this->cartRepository->save($cart);
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
            if (empty($item['cart_item_id'])) {
                throw new GraphQlInputException(__('Required parameter "cart_item_id" for "cart_items" is missing.'));
            }
            $itemId = (int)$item['cart_item_id'];
            $customizableOptions = $item['customizable_options'] ?? [];

            if (count($customizableOptions) === 0 && !isset($item['quantity'])) {
                throw new GraphQlInputException(__('Required parameter "quantity" for "cart_items" is missing.'));
            }
            $quantity = (float)$item['quantity'];

            if ($quantity <= 0.0) {
                $this->cartItemRepository->deleteById((int)$cart->getId(), $itemId);
            } else {
                $this->updateCartItem->execute($cart, $itemId, $quantity, $customizableOptions);
            }
        }
    }
}
