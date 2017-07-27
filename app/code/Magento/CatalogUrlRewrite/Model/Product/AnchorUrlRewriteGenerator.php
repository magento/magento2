<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

class AnchorUrlRewriteGenerator extends BaseUrlRewriteGenerator
{
    /** @var ProductUrlPathGenerator */
    protected $urlPathGenerator;

    /** @var UrlRewriteFactory */
    protected $urlRewriteFactory;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /**
     * @param ProductUrlPathGenerator $urlPathGenerator
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param UrlFinderInterface $urlFinder
     */
    public function __construct(
        ProductUrlPathGenerator $urlPathGenerator,
        UrlRewriteFactory $urlRewriteFactory,
        CategoryRepositoryInterface $categoryRepository,
        UrlFinderInterface $urlFinder
    ) {
        $this->urlPathGenerator = $urlPathGenerator;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->categoryRepository = $categoryRepository;
        $this->urlFinder = $urlFinder;
    }

    /**
     * Generate product rewrites for anchor categories
     *
     * @param int $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     * @param MergeDataProvider $urlRewrites
     * @return UrlRewrite[]
     */
    public function generate(
        $storeId,
        Product $product,
        ObjectRegistry $productCategories,
        MergeDataProvider $urlRewrites = null
    ) {
        $this->urlRewrites = $urlRewrites;

        $urls = [];
        foreach ($productCategories->getList() as $category) {
            $anchorCategoryIds = $category->getAnchorsAbove();
            if ($anchorCategoryIds) {
                foreach ($anchorCategoryIds as $anchorCategoryId) {
                    $anchorCategory = $this->categoryRepository->get($anchorCategoryId);

                    if (!in_array(
                        $anchorCategory->getParentId(),
                        [Category::ROOT_CATEGORY_ID, Category::TREE_ROOT_ID]
                    )) {
                        $paths = [
                            $this->urlPathGenerator->getUrlPathWithSuffix($product, $storeId, $anchorCategory),
                            $this->urlPathGenerator->getUrlPathWithIdAndSuffix($product, $storeId, $anchorCategory)
                        ];

                        $requestPath = $this->checkRequestPaths($paths, $product->getId(), $storeId);

                        $urls[] = $this->urlRewriteFactory->create()
                            ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                            ->setEntityId($product->getId())
                            ->setRequestPath($requestPath)
                            ->setTargetPath(
                                $this->urlPathGenerator->getCanonicalUrlPath(
                                    $product,
                                    $anchorCategory
                                )
                            )
                            ->setStoreId($storeId)
                            ->setMetadata(['category_id' => $anchorCategory->getId()]);
                    }
                }
            }
        }

        return $urls;
    }
}
