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
use Magento\Quote\Model\Cart\AddProductsToCart as AddProductsToCartService;
use Magento\Quote\Model\Cart\Data\AddProductsToCartOutput;
use Magento\Quote\Model\Cart\Data\CartItemFactory;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Model\Cart\Data\Error;

/**
 * Resolver for addProductsToCart mutation
 *
 * @inheritdoc
 */
class AddProductsToCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var AddProductsToCartService
     */
    private $addProductsToCartService;

    /**
     * @param GetCartForUser $getCartForUser
     * @param AddProductsToCartService $addProductsToCart
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        AddProductsToCartService $addProductsToCart
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->addProductsToCartService = $addProductsToCart;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['cartId'])) {
            throw new GraphQlInputException(__('Required parameter "cartId" is missing'));
        }
        if (empty($args['cartItems']) || !is_array($args['cartItems'])
        ) {
            throw new GraphQlInputException(__('Required parameter "cartItems" is missing'));
        }

        $maskedCartId = $args['cartId'];
        $cartItemsData = $args['cartItems'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        // Shopping Cart validation
        $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        $cartItems = [];
        foreach ($cartItemsData as $cartItemData) {
            $cartItems[] = (new CartItemFactory())->create($cartItemData);
        }

        /** @var AddProductsToCartOutput $addProductsToCartOutput */
        $addProductsToCartOutput = $this->addProductsToCartService->execute($maskedCartId, $cartItems);

        return [
            'cart' => [
                'model' => $addProductsToCartOutput->getCart(),
            ],
            'user_errors' => array_map(
                function (Error $error) {
                    return [
                        'code' => $error->getCode(),
                        'message' => $error->getMessage(),
                        'path' => [$error->getCartItemPosition()]
                    ];
                },
                $addProductsToCartOutput->getErrors()
            )
        ];
    }
}
