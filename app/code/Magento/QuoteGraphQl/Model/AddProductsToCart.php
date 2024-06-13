<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Api\ErrorInterface;
use Magento\Quote\Model\Cart\Data\AddProductsToCartOutput;
use Magento\Quote\Model\Cart\Data\CartItemFactory;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Model\Cart\AddProductsToCart as AddProductsToCartService;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\CartItem\PrecursorInterface;

class AddProductsToCart
{
    /**
     * @param GetCartForUser $getCartForUser
     * @param AddProductsToCartService $addProductsToCartService
     * @param ScopeConfigInterface $scopeConfig
     * @param PrecursorInterface $cartItemPrecursor
     */
    public function __construct(
        private readonly GetCartForUser $getCartForUser,
        private readonly AddProductsToCartService $addProductsToCartService,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly PrecursorInterface $cartItemPrecursor
    ) {
    }

    /**
     * Add products in cart
     *
     * @param ContextInterface $context
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function execute($context, ?array $args): array
    {
        $maskedCartId = $args['cartId'];
        $cartItemsData = $args['cartItems'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        // Shopping Cart validation
        $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        $cartItemsData = $this->cartItemPrecursor->process($cartItemsData, $context);
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
                function (ErrorInterface $error) {
                    return [
                        'code' => $error->getCode(),
                        'message' => $error->getMessage(),
                        'path' => [$error->getCartItemPosition()],
                        'quantity' => $this->isStockItemMessageEnabled() ? $error->getQuantity() : null
                    ];
                },
                array_merge($addProductsToCartOutput->getErrors(), $this->cartItemPrecursor->getErrors())
            )
        ];
    }

    /**
     * Check inventory option available message
     *
     * @return bool
     */
    private function isStockItemMessageEnabled(): bool
    {
        return (int) $this->scopeConfig->getValue('cataloginventory/options/not_available_message') === 1;
    }
}
