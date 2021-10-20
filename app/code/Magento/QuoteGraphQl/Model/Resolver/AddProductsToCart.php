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
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Model\Cart\AddProductsToCart as AddProductsToCartService;
use Magento\Quote\Model\Cart\Data\AddProductsToCartOutput;
use Magento\Quote\Model\Cart\Data\CartItemFactory;
use Magento\Quote\Model\QuoteMutexInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Model\Cart\Data\Error;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\Processor\ItemDataProcessorInterface;

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
     * @var ItemDataProcessorInterface
     */
    private $itemDataProcessor;

    /**
     * @var QuoteMutexInterface
     */
    private $quoteMutex;

    /**
     * @param GetCartForUser $getCartForUser
     * @param AddProductsToCartService $addProductsToCart
     * @param ItemDataProcessorInterface $itemDataProcessor
     * @param QuoteMutexInterface $quoteMutex
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        AddProductsToCartService $addProductsToCart,
        ItemDataProcessorInterface $itemDataProcessor,
        QuoteMutexInterface $quoteMutex
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->addProductsToCartService = $addProductsToCart;
        $this->itemDataProcessor = $itemDataProcessor;
        $this->quoteMutex = $quoteMutex;
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

        return $this->quoteMutex->execute(
            [$args['cartId']],
            \Closure::fromCallable([$this, 'run']),
            [$context, $args]
        );
    }

    /**
     * Run the resolver.
     *
     * @param ContextInterface $context
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function run($context, ?array $args): array
    {
        $maskedCartId = $args['cartId'];
        $cartItemsData = $args['cartItems'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        // Shopping Cart validation
        $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        $cartItems = [];
        foreach ($cartItemsData as $cartItemData) {
            if (!$this->itemIsAllowedToCart($cartItemData, $context)) {
                continue;
            }
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

    /**
     * Check if the item can be added to cart
     *
     * @param array $cartItemData
     * @param ContextInterface $context
     * @return bool
     */
    private function itemIsAllowedToCart(array $cartItemData, ContextInterface $context): bool
    {
        $cartItemData = $this->itemDataProcessor->process($cartItemData, $context);
        if (isset($cartItemData['grant_checkout']) && $cartItemData['grant_checkout'] === false) {
            return false;
        }

        return true;
    }
}
