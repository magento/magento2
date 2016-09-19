<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator;
use Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Class ProductUrlRewriteGenerator
 * @package Magento\CatalogUrlRewrite\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryBasedProductRewriteGenerator
{
    /**
     * @var ProductScopeRewriteGenerator
     */
    private $productScopeRewriteGenerator;

    /**
     * @param ProductScopeRewriteGenerator $productUrlRewriteGenerator
     */
    public function __construct(
        ProductScopeRewriteGenerator $productScopeRewriteGenerator
    ) {
        $this->productScopeRewriteGenerator = $productScopeRewriteGenerator;
    }

    /**
     * Generate product url rewrites based on category
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function generate(Product $product, Category $category)
    {
        if ($product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE) {
            return [];
        }

        $storeId = $product->getStoreId();

        $urls = $this->productScopeRewriteGenerator->isGlobalScope($storeId)
            ? $this->productScopeRewriteGenerator->generateForGlobalScope([$category], $product)
            : $this->productScopeRewriteGenerator->generateForSpecificStoreView($storeId, [$category], $product);

        $this->product = null;
        return $urls;
    }
}
