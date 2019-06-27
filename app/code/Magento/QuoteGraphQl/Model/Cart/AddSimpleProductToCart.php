<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\Quote;

/**
 * Add simple product to cart
 */
class AddSimpleProductToCart
{
    /**
     * @var CreateBuyRequest
     */
    private $createBuyRequest;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param CreateBuyRequest $createBuyRequest
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        CreateBuyRequest $createBuyRequest
    ) {
        $this->productRepository = $productRepository;
        $this->createBuyRequest = $createBuyRequest;
    }

    /**
     * Add simple product to cart
     *
     * @param Quote $cart
     * @param array $cartItemData
     * @return void
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Quote $cart, array $cartItemData): void
    {
        $sku = $this->extractSku($cartItemData);
        $quantity = $this->extractQuantity($cartItemData);
        $customizableOptions = $cartItemData['customizable_options'] ?? [];

        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('Could not find a product with SKU "%sku"', ['sku' => $sku]));
        }

        try {
            $result = $cart->addProduct($product, $this->createBuyRequest->execute($quantity, $customizableOptions));
        } catch (\Exception $e) {
            throw new GraphQlInputException(
                __(
                    'Could not add the product with SKU %sku to the shopping cart: %message',
                    ['sku' => $sku, 'message' => $e->getMessage()]
                )
            );
        }

        if (is_string($result)) {
            throw new GraphQlInputException(__($result));
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
        if (!isset($cartItemData['data']['sku']) || empty($cartItemData['data']['sku'])) {
            throw new GraphQlInputException(__('Missed "sku" in cart item data'));
        }
        return (string)$cartItemData['data']['sku'];
    }

    /**
     * Extract quantity from cart item data
     *
     * @param array $cartItemData
     * @return float
     * @throws GraphQlInputException
     */
    private function extractQuantity(array $cartItemData): float
    {
        if (!isset($cartItemData['data']['quantity'])) {
            throw new GraphQlInputException(__('Missed "qty" in cart item data'));
        }
        $quantity = (float)$cartItemData['data']['quantity'];

        if ($quantity <= 0) {
            throw new GraphQlInputException(
                __('Please enter a number greater than 0 in this field.')
            );
        }
        return $quantity;
    }
}
