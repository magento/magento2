<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\BuyRequest\BuyRequestBuilder;

/**
 * Add simple product to cart mutation
 */
class AddSimpleProductToCart
{
    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var BuyRequestBuilder
     */
    private $buyRequestBuilder;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param BuyRequestBuilder $buyRequestBuilder
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        BuyRequestBuilder        $buyRequestBuilder
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->buyRequestBuilder = $buyRequestBuilder;
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
        $cartItemData['model'] = $cart;
        $sku = $this->extractSku($cartItemData);

        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToFilter(ProductInterface::SKU, $sku)
            ->addWebsiteFilter([$cart->getStore()->getWebsiteId()])
            ->load();
        /** @var ProductInterface $product */
        $product = $productCollection->getFirstItem();
        if (!$product->getId()) {
            throw new GraphQlNoSuchEntityException(__('Could not find a product with SKU "%sku"', ['sku' => $sku]));
        }

        try {
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
}
