<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Cart\BuyRequest\BuyRequestBuilder;
use Magento\Quote\Model\Cart\Data\AddProductsToCartOutput;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\Message\MessageInterface;

/**
 * Unified approach to add products to the Shopping Cart.
 * Client code must validate, that customer is eligible to call service with provided {cartId} and {cartItems}
 */
class AddProductsToCart
{
    /**
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param BuyRequestBuilder $requestBuilder
     * @param ProductReaderInterface $productReader
     * @param AddProductsToCartError $error
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        private readonly BuyRequestBuilder $requestBuilder,
        private readonly ProductReaderInterface $productReader,
        private readonly AddProductsToCartError $error,
        private readonly StockRegistryInterface $stockRegistry
    ) {
    }

    /**
     * Add cart items to the cart
     *
     * @param string $maskedCartId
     * @param Data\CartItem[] $cartItems
     * @return AddProductsToCartOutput
     * @throws NoSuchEntityException Could not find a Cart with provided $maskedCartId
     */
    public function execute(string $maskedCartId, array $cartItems): AddProductsToCartOutput
    {
        $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        $cart = $this->cartRepository->get($cartId);
        $allErrors = [];

        $failedCartItems = $this->addItemsToCart($cart, $cartItems);
        $saveCart = empty($failedCartItems);
        if (!empty($failedCartItems)) {
            /* Check if some cart items were successfully added to the cart */
            if (count($failedCartItems) < count($cartItems)) {
                /* Revert changes introduced by add to cart processes in case of an error */
                $cart->getItemsCollection()->clear();
                $newFailedCartItems = $this->addItemsToCart($cart, array_diff_key($cartItems, $failedCartItems));
                $failedCartItems += $newFailedCartItems;
                $saveCart = empty($newFailedCartItems);
            }
            foreach (array_keys($cartItems) as $cartItemPosition) {
                if (isset($failedCartItems[$cartItemPosition])) {
                    array_push($allErrors, ...$failedCartItems[$cartItemPosition]);
                }
            }
        }
        if ($saveCart) {
            $this->cartRepository->save($cart);
        }
        if (count($allErrors) !== 0) {
            /* Revert changes introduced by add to cart processes in case of an error */
            $cart->getItemsCollection()->clear();
        }

        return $this->prepareErrorOutput($cart, $allErrors);
    }

    /**
     * Add cart items to cart
     *
     * @param Quote $cart
     * @param array $cartItems
     * @return array
     */
    public function addItemsToCart(Quote $cart, array $cartItems): array
    {
        $failedCartItems = [];
        // add new cart items for preload
        $skus = \array_map(
            function ($item) {
                return $item->getSku();
            },
            $cartItems
        );
        $this->productReader->loadProducts($skus, $cart->getStoreId());
        foreach ($cartItems as $cartItemPosition => $cartItem) {
            $product = $this->productReader->getProductBySku($cartItem->getSku());
            $stockItemQuantity = 0.0;
            if ($product) {
                $stockItem = $this->stockRegistry->getStockItem(
                    $product->getId(),
                    $cart->getStore()->getWebsiteId()
                );
                $stockItemQuantity = $stockItem->getQty() - $stockItem->getMinQty();
            }
            $errors = $this->addItemToCart($cart, $cartItem, $cartItemPosition, $stockItemQuantity);
            if ($errors) {
                $failedCartItems[$cartItemPosition] = $errors;
            }
        }

        return $failedCartItems;
    }

    /**
     * Adds a particular item to the shopping cart
     *
     * @param Quote $cart
     * @param Data\CartItem $cartItem
     * @param int $cartItemPosition
     * @param float $stockItemQuantity
     * @return array
     */
    private function addItemToCart(
        Quote $cart,
        Data\CartItem $cartItem,
        int $cartItemPosition,
        float $stockItemQuantity
    ): array {
        $sku = $cartItem->getSku();
        $errors = [];
        $result = null;

        if ($cartItem->getQuantity() <= 0) {
            $errors[] = $this->error->create(
                __('The product quantity should be greater than 0')->render(),
                $cartItemPosition,
                $stockItemQuantity
            );
        }

        $productBySku = $this->productReader->getProductBySku($sku);
        $product = isset($productBySku) ? clone $productBySku : null;

        if (!$product || !$product->isSaleable() || !$product->isAvailable()) {
            return [
                $this->error->create(
                    __('Could not find a product with SKU "%sku"', ['sku' => $sku])->render(),
                    $cartItemPosition,
                    $stockItemQuantity
                )
            ];
        }

        try {
            $result = $cart->addProduct($product, $this->requestBuilder->build($cartItem));
        } catch (\Throwable $e) {
            $errors[] = $this->error->create(
                __($e->getMessage())->render(),
                $cartItemPosition,
                $stockItemQuantity
            );
        }

        if (is_string($result)) {
            foreach (array_unique(explode("\n", $result)) as $error) {
                $errors[] = $this->error->create(__($error)->render(), $cartItemPosition, $stockItemQuantity);
            }
        }

        return $errors;
    }

    /**
     * Creates a new output from existing errors
     *
     * @param Quote $cart
     * @param array $errors
     * @return AddProductsToCartOutput
     */
    private function prepareErrorOutput(Quote $cart, array $errors = []): AddProductsToCartOutput
    {
        $output = new AddProductsToCartOutput($cart, $errors);
        $cart->setHasError(false);

        return $output;
    }
}
