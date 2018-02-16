<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Generates product url rewrites based on category.
 */
class CategoryBasedProductRewriteGenerator
{
    /**
     * Generates url rewrites for different scopes.
     *
     * @var ProductScopeRewriteGenerator
     */
    private $productScopeRewriteGenerator;

    /**
     * @param ProductScopeRewriteGenerator $productScopeRewriteGenerator
     * @return void
     */
    public function __construct(
        ProductScopeRewriteGenerator $productScopeRewriteGenerator
    ) {
        $this->productScopeRewriteGenerator = $productScopeRewriteGenerator;
    }

    /**
     * Generates product url rewrites based on category
     *
     * @param Product $product
     * @param Category $category
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function generate(Product $product, Category $category, $rootCategoryId = null)
    {
        if ($product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE) {
            return [];
        }

        $storeId = $product->getStoreId();

        $urls = $this->productScopeRewriteGenerator->isGlobalScope($storeId)
            ? $this->productScopeRewriteGenerator->generateForGlobalScope([$category], $product, $rootCategoryId)
            : $this->productScopeRewriteGenerator->generateForSpecificStoreView(
                $storeId,
                [$category],
                $product,
                $rootCategoryId
            );

        return $urls;
    }
}
