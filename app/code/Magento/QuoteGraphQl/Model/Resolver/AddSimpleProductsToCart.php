<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\QuoteGraphQl\Model\Cart\AddProductsToCart;
use Magento\QuoteGraphQl\Model\Cart\ExtractDataFromCart;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * Add simple products to cart GraphQl resolver
 * {@inheritdoc}
 */
class AddSimpleProductsToCart implements ResolverInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var AddProductsToCart
     */
    private $addProductsToCart;

    /**
     * @var ExtractDataFromCart
     */
    private $extractDataFromCart;

    /**
     * @param ArrayManager $arrayManager
     * @param GetCartForUser $getCartForUser
     * @param AddProductsToCart $addProductsToCart
     * @param ExtractDataFromCart $extractDataFromCart
     */
    public function __construct(
        ArrayManager $arrayManager,
        GetCartForUser $getCartForUser,
        AddProductsToCart $addProductsToCart,
        ExtractDataFromCart $extractDataFromCart
    ) {
        $this->arrayManager = $arrayManager;
        $this->getCartForUser = $getCartForUser;
        $this->addProductsToCart = $addProductsToCart;
        $this->extractDataFromCart = $extractDataFromCart;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $cartHash = $this->arrayManager->get('input/cart_id', $args);
        $cartItems = $this->arrayManager->get('input/cartItems', $args);

        if (!isset($cartHash)) {
            throw new GraphQlInputException(__('Missing key "cart_id" in cart data'));
        }

        if (!isset($cartItems) || !is_array($cartItems) || empty($cartItems)) {
            throw new GraphQlInputException(__('Missing key "cartItems" in cart data'));
        }

        $currentUserId = $context->getUserId();
        $cart = $this->getCartForUser->execute((string)$cartHash, $currentUserId);

        $this->addProductsToCart->execute($cart, $cartItems);
        $cartData = $this->extractDataFromCart->execute($cart);

        return [
            'cart' => $cartData,
        ];
    }
}
