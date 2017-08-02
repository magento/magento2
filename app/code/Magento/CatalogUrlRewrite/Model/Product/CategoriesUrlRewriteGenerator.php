<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

class CategoriesUrlRewriteGenerator extends BaseUrlRewriteGenerator
{
    /** @var ProductUrlPathGenerator */
    protected $productUrlPathGenerator;

    /** @var UrlRewriteFactory */
    protected $urlRewriteFactory;

    /**
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param UrlFinderInterface $urlFinder
     */
    public function __construct(
        ProductUrlPathGenerator $productUrlPathGenerator,
        UrlRewriteFactory $urlRewriteFactory,
        UrlFinderInterface $urlFinder
    ) {
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlFinder = $urlFinder;
    }

    /**
     * Generate product rewrites with categories
     *
     * @param int $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     * @param MergeDataProvider|null $urlRewrites
     * @return UrlRewrite[]
     */
    public function generate(
        $storeId,
        Product $product,
        ObjectRegistry $productCategories,
        $urlRewrites = null
    ) {
        $this->urlRewrites = $urlRewrites;
        $urls = [];
        foreach ($productCategories->getList() as $category) {

            $paths = [
                $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category),
                $this->productUrlPathGenerator->getUrlPathWithIdAndSuffix($product, $storeId, $category)
            ];

            $requestPath = $this->checkRequestPaths($paths, $product->getId(), $storeId);

            $urls[] = $this->urlRewriteFactory->create()
                ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                ->setEntityId($product->getId())
                ->setRequestPath($requestPath)
                ->setTargetPath($this->productUrlPathGenerator->getCanonicalUrlPath($product, $category))
                ->setStoreId($storeId)
                ->setMetadata(['category_id' => $category->getId()]);
        }
        return $urls;
    }
}
