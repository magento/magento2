<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

/**
 * Class \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator
 *
 * @since 2.0.0
 */
class CanonicalUrlRewriteGenerator
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
     * @since 2.0.0
     */
    protected $productUrlPathGenerator;

    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory
     * @since 2.0.0
     */
    protected $urlRewriteFactory;

    /**
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param UrlRewriteFactory $urlRewriteFactory
     * @since 2.0.0
     */
    public function __construct(ProductUrlPathGenerator $productUrlPathGenerator, UrlRewriteFactory $urlRewriteFactory)
    {
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->urlRewriteFactory = $urlRewriteFactory;
    }

    /**
     * Generate product rewrites without categories
     *
     * @param int $storeId
     * @param Product $product
     * @return UrlRewrite[]
     * @since 2.0.0
     */
    public function generate($storeId, Product $product)
    {
        return [
            $this->urlRewriteFactory->create()
                ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                ->setEntityId($product->getId())
                ->setRequestPath($this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId))
                ->setTargetPath($this->productUrlPathGenerator->getCanonicalUrlPath($product))
                ->setStoreId($storeId)
        ];
    }
}
