<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder;

class CategoriesUrlRewriteGenerator
{
    /** @var ProductUrlPathGenerator */
    protected $productUrlPathGenerator;

    /** @var UrlRewriteBuilder */
    protected $urlRewriteBuilder;

    /**
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param UrlRewriteBuilder $urlRewriteBuilder
     */
    public function __construct(ProductUrlPathGenerator $productUrlPathGenerator, UrlRewriteBuilder $urlRewriteBuilder)
    {
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->urlRewriteBuilder = $urlRewriteBuilder;
    }

    /**
     * Generate list based on categories
     *
     * @param int $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     * @return UrlRewrite[]
     */
    public function generate($storeId, Product $product, ObjectRegistry $productCategories)
    {
        $urls = [];
        foreach ($productCategories->getList() as $category) {
            $urls[] = $this->urlRewriteBuilder
                ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                ->setEntityId($product->getId())
                ->setRequestPath($this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category))
                ->setTargetPath($this->productUrlPathGenerator->getCanonicalUrlPath($product, $category))
                ->setStoreId($storeId)
                ->setMetadata(['category_id' => $category->getId()])
                ->create();
        }
        return $urls;
    }
}
