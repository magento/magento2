<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;

/**
 * Class ProductUrlRewriteGenerator
 * @package Magento\CatalogUrlRewrite\Model
 */
class CategoryProductUrlPathGenerator
{
    /**
     * @var ProductScopeRewriteGenerator
     */
    private $productScopeRewriteGenerator;

    /**
     * @var Visibility
     */
    private $productVisibility;

    /**
     * @param ProductScopeRewriteGenerator $productScopeRewriteGenerator
     * @param Visibility|null $productVisibility
     */
    public function __construct(
        ProductScopeRewriteGenerator $productScopeRewriteGenerator,
        Visibility $productVisibility = null
    ) {
        $this->productScopeRewriteGenerator = $productScopeRewriteGenerator;
        $this->productVisibility = $productVisibility ?: ObjectManager::getInstance()->get(Visibility::class);
    }

    /**
     * Generate product url rewrites based on all product categories
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function generate(Product $product, $rootCategoryId = null)
    {
        if (in_array($product->getVisibility(), $this->productVisibility->getNotVisibleInSiteIds())) {
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
