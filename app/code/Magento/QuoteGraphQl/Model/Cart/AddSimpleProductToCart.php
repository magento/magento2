<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\BuyRequest\BuyRequestBuilder;
use Magento\CatalogInventory\Api\StockStateInterface;

/**
 * Add simple product to cart
 */
class AddSimpleProductToCart
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var BuyRequestBuilder
     */
    private $buyRequestBuilder;

    /**
     * @var StockStateInterface
     */
    private $stockState;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param BuyRequestBuilder $buyRequestBuilder
     * @param StockStateInterface $stockState
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        BuyRequestBuilder $buyRequestBuilder,
        StockStateInterface $stockState
    ) {
        $this->productRepository = $productRepository;
        $this->buyRequestBuilder = $buyRequestBuilder;
        $this->stockState = $stockState;
    }

    /**
     * Add simple product to cart
     *
     * @param Quote $cart
     * @param array $cartItemData
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(Quote $cart, array $cartItemData): void
    {
        $sku = $this->extractSku($cartItemData);
        $childSku = $this->extractChildSku($cartItemData);
        $childSkuQty = $this->extractChildSkuQuantity($cartItemData);
        try {
            $product = $this->productRepository->get($sku, false, null, true);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('Could not find a product with SKU "%sku"', ['sku' => $sku]));
        }

        if ($childSku) {
            $childProduct = $this->productRepository->get($childSku, false, null, true);

            $result = $this->stockState->checkQuoteItemQty(
                $childProduct->getId(), $childSkuQty, $childSkuQty, $childSkuQty, $cart->getStoreId()
            );

            if ($result->getHasError() ) {
                throw new GraphQlInputException(
                    __(
                        'Could not add the product with SKU %sku to the shopping cart: %message',
                        ['sku' => $childSku, 'message' => __($result->getMessage())]
                    )
                );
            }
        }

        try {
           $buyRequest = $this->buyRequestBuilder->build($cartItemData);
           // Some options might be disabled and not available
           if (empty($buyRequest['super_attribute'])) {
               throw new LocalizedException(
                   __('The product with SKU %sku is out of stock.', ['sku' => $childSku])
               );
           }
           $result = $cart->addProduct($product, $this->buyRequestBuilder->build($cartItemData));
        } catch (Exception $e) {
            throw new GraphQlInputException(
                __(
                    'Could not add the product with SKU %sku to the shopping cart: %message',
                    ['sku' => $sku, 'message' => $e->getMessage()]
                )
            );
        }

        if (is_string($result)) {
            $e = new GraphQlInputException(__('Cannot add product to cart'));
            $errors = array_unique(explode("\n", $result));
            foreach ($errors as $error) {
                $e->addError(new GraphQlInputException(__($error)));
            }
            throw $e;
        }
    }

    /**
     * Extract SKU from cart item data
     *
     * @param array $cartItemData
     * @return string
     * @throws GraphQlInputException
     */
    private function extractSku(array $cartItemData): string
    {
        // Need to keep this for configurable product and backward compatibility.
        if (!empty($cartItemData['parent_sku'])) {
            return (string)$cartItemData['parent_sku'];
        }
        if (empty($cartItemData['data']['sku'])) {
            throw new GraphQlInputException(__('Missed "sku" in cart item data'));
        }
        return (string)$cartItemData['data']['sku'];
    }

    /**
     * Extract option child SKU from cart item data
     *
     * @param array $cartItemData
     * @return string
     * @throws GraphQlInputException
     */
    private function extractChildSku(array $cartItemData): ?string
    {
        if (isset($cartItemData['data']['sku'])) {
            return (string)$cartItemData['data']['sku'];
        }
    }

    /**
     * Extract option child SKU from cart item data
     *
     * @param array $cartItemData
     * @return string
     * @throws GraphQlInputException
     */
    private function extractChildSkuQuantity(array $cartItemData): ?string
    {
        if (empty($cartItemData['data']['quantity'])) {
            throw new GraphQlInputException(__('Missed "quantity" in cart item data'));
        }
        return (string)$cartItemData['data']['quantity'];
    }
}
