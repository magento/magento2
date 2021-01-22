<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Magento\WishlistGraphQl\Model\CartItems;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Wishlist\Model\Item;

/**
 * Building cart items request for add to cart form wishlist buy request
 */
class CartItemsRequestBuilder
{
    /**
     * @var CartItemsRequestDataProviderInterface[]
     */
    private $providers;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param array $providers
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        array $providers = []
    ) {
        $this->productRepository = $productRepository;
        $this->providers = $providers;
    }

    /**
     * Build wishlist cart item request for adding to cart
     *
     * @param Item $wishlistItem
     * @return array
     */
    public function build(Item $wishlistItem): array
    {
        $product = $this->productRepository->getById($wishlistItem->getProductId());
        $parentsku = $product->getSku();
        $cartItems['quantity'] = floatval($wishlistItem->getQty());
        $cartItems['sku'] = $parentsku;

        foreach ($this->providers as $provider) {
            $cartItems = array_merge_recursive($cartItems, $provider->execute($wishlistItem, $parentsku));
        }
        return $cartItems;
    }
}
