<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

class CanonicalUrlRewriteGenerator
{
    /** @var ProductUrlPathGenerator */
    protected $productUrlPathGenerator;

    /** @var UrlRewriteFactory */
    protected $urlRewriteFactory;

    /**
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param UrlRewriteFactory $urlRewriteFactory
     */
    public function __construct(ProductUrlPathGenerator $productUrlPathGenerator, UrlRewriteFactory $urlRewriteFactory)
    {
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->urlRewriteFactory = $urlRewriteFactory;
    }

    /**
     * Generate list based on store view
     *
     * @param int $storeId
     * @param Product $product
     * @return UrlRewrite[]
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
