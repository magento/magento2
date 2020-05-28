<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Model\Quote;

/**
 * Fetch Product models corresponding to a cart's items
 */
class GetCartProducts
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $cartItemIds, 'in')->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();

        return $products;
    }
}
