<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Quote\Model\Quote;

/**
 * Fetch Product models corresponding to a cart's items
 */
class GetCartProducts
{
    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Get product models based on items in cart
     *
     * @param Quote $cart
     * @return ProductInterface[]
     */
    public function execute(Quote $cart): array
    {
        $cartItems = $cart->getAllVisibleItems();
        if (empty($cartItems)) {
            return [];
        }
        $cartItemIds = \array_map(
            function ($item) {
                return $item->getProduct()->getId();
            },
            $cartItems
        );

        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addIdFilter($cartItemIds)
            ->setFlag('has_stock_status_filter', true);
        $products = $productCollection->getItems();

        return $products;
    }
}
