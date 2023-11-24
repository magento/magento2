<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart;

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
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var BuyRequestBuilder
     */
    private $requestBuilder;

    /**
     * @var ProductReaderInterface
     */
    private $productReader;

    /**
     * @var AddProductsToCartError
     */
    private $error;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param BuyRequestBuilder $requestBuilder
     * @param ProductReaderInterface $productReader
     * @param AddProductsToCartError $addProductsToCartError
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        BuyRequestBuilder $requestBuilder,
        ProductReaderInterface $productReader,
        AddProductsToCartError $addProductsToCartError
    ) {
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->requestBuilder = $requestBuilder;
        $this->productReader = $productReader;
        $this->error = $addProductsToCartError;
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
        if ($cart->getData('has_error')) {
            $errors = $cart->getErrors();

            /** @var MessageInterface $error */
            foreach ($errors as $error) {
                $allErrors[] = $this->error->create($error->getText());
            }
        }

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
            $errors = $this->addItemToCart($cart, $cartItem, $cartItemPosition);
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
     * @return array
     */
    private function addItemToCart(Quote $cart, Data\CartItem $cartItem, int $cartItemPosition): array
    {
        $sku = $cartItem->getSku();
        $errors = [];
        $result = null;

        if ($cartItem->getQuantity() <= 0) {
            $errors[] = $this->error->create(
                __('The product quantity should be greater than 0')->render(),
                $cartItemPosition
            );
        } else {
            $product = $this->productReader->getProductBySku($sku);
            if (!$product || !$product->isSaleable() || !$product->isAvailable()) {
                $errors[] = $this->error->create(
                    __('Could not find a product with SKU "%sku"', ['sku' => $sku])->render(),
                    $cartItemPosition
                );
            } else {
                try {
                    $result = $cart->addProduct($product, $this->requestBuilder->build($cartItem));
                } catch (\Throwable $e) {
                    $errors[] = $this->error->create(
                        __($e->getMessage())->render(),
                        $cartItemPosition
                    );
                }
            }

            if (is_string($result)) {
                foreach (array_unique(explode("\n", $result)) as $error) {
                    $errors[] = $this->error->create(__($error)->render(), $cartItemPosition);
                }
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
