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
     * @param ProductScopeRewriteGenerator $productScopeRewriteGenerator
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
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function generate(Product $product, Category $category, $rootCategoryId = null)
    {
        if ($product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE) {
            return [];
        }

        $storeId = $product->getStoreId();

        $productCategories = $product->getCategoryCollection()
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');

        $urls = $this->productScopeRewriteGenerator->isGlobalScope($storeId)
            ? $this->productScopeRewriteGenerator->generateForGlobalScope(
                $productCategories,
                $product,
                $rootCategoryId
            )
            : $this->productScopeRewriteGenerator->generateForSpecificStoreView(
                $storeId,
                $productCategories,
                $product,
                $rootCategoryId
            );

        return $urls;
    }
}
