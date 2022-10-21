<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
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
    /**#@+
     * Error message codes
     */
    private const ERROR_PRODUCT_NOT_FOUND = 'PRODUCT_NOT_FOUND';
    private const ERROR_INSUFFICIENT_STOCK = 'INSUFFICIENT_STOCK';
    private const ERROR_NOT_SALABLE = 'NOT_SALABLE';
    private const ERROR_UNDEFINED = 'UNDEFINED';
    /**#@-*/

    /**
     * List of error messages and codes.
     */
    private const MESSAGE_CODES = [
        'Could not find a product with SKU' => self::ERROR_PRODUCT_NOT_FOUND,
        'The required options you selected are not available' => self::ERROR_NOT_SALABLE,
        'Product that you are trying to add is not available.' => self::ERROR_NOT_SALABLE,
        'This product is out of stock' => self::ERROR_INSUFFICIENT_STOCK,
        'There are no source items' => self::ERROR_NOT_SALABLE,
        'The fewest you may purchase is' => self::ERROR_INSUFFICIENT_STOCK,
        'The most you may purchase is' => self::ERROR_INSUFFICIENT_STOCK,
        'The requested qty is not available' => self::ERROR_INSUFFICIENT_STOCK,
    ];

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

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
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param BuyRequestBuilder $requestBuilder
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CartRepositoryInterface $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        BuyRequestBuilder $requestBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder = null
    ) {
        $this->productRepository = $productRepository;
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->requestBuilder = $requestBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: ObjectManager::getInstance()->get(
            SearchCriteriaBuilder::class
        );
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
                $allErrors[] = $this->createError($error->getText());
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
        $cartItemSkus = \array_map(
            function ($item) {
                return $item->getSku();
            },
            $cartItems
        );

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('sku', $cartItemSkus, 'in')->create();
        // getList() call caches product models in runtime cache
        $this->productRepository->getList($searchCriteria)->getItems();

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
        $product = null;

        if ($cartItem->getQuantity() <= 0) {
            $errors[] = $this->createError(
                __('The product quantity should be greater than 0')->render(),
                $cartItemPosition
            );
        } else {
            try {
                $product = $this->productRepository->get($sku, false, $cart->getStoreId(), false);
            } catch (NoSuchEntityException $e) {
                $errors[] = $this->createError(
                    __('Could not find a product with SKU "%sku"', ['sku' => $sku])->render(),
                    $cartItemPosition
                );
            }

            if ($product !== null) {
                try {
                    $result = $cart->addProduct($product, $this->requestBuilder->build($cartItem));
                } catch (\Throwable $e) {
                    $errors[] = $this->createError(
                        __($e->getMessage())->render(),
                        $cartItemPosition
                    );
                }

                if (is_string($result)) {
                    foreach (array_unique(explode("\n", $result)) as $error) {
                        $errors[] = $this->createError(__($error)->render(), $cartItemPosition);
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Returns an error object
     *
     * @param string $message
     * @param int $cartItemPosition
     * @return Data\Error
     */
    private function createError(string $message, int $cartItemPosition = 0): Data\Error
    {
        return new Data\Error(
            $message,
            $this->getErrorCode($message),
            $cartItemPosition
        );
    }

    /**
     * Get message error code.
     *
     * TODO: introduce a separate class for getting error code from a message
     *
     * @param string $message
     * @return string
     */
    private function getErrorCode(string $message): string
    {
        foreach (self::MESSAGE_CODES as $codeMessage => $code) {
            if (false !== stripos($message, $codeMessage)) {
                return $code;
            }
        }

        /* If no code was matched, return the default one */
        return self::ERROR_UNDEFINED;
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
