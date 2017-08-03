<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Class ProductUrlRewriteGenerator
 * @package Magento\CatalogUrlRewrite\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class ProductUrlRewriteGenerator
{
    /**
     * Entity type code
     */
    const ENTITY_TYPE = 'product';

    /**
     * @deprecated 2.2.0
     * @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService
     * @since 2.0.0
     */
    protected $storeViewService;

    /**
     * @var \Magento\Catalog\Model\Product
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $product;

    /**
     * @deprecated 2.2.0
     * @var \Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator
     * @since 2.0.0
     */
    protected $currentUrlRewritesRegenerator;

    /**
     * @deprecated 2.2.0
     * @var \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator
     * @since 2.0.0
     */
    protected $categoriesUrlRewriteGenerator;

    /**
     * @deprecated 2.2.0
     * @var \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator
     * @since 2.0.0
     */
    protected $canonicalUrlRewriteGenerator;

    /**
     * @deprecated 2.2.0
     * @var \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory
     * @since 2.0.0
     */
    protected $objectRegistryFactory;

    /**
     * @deprecated 2.2.0
     * @var \Magento\CatalogUrlRewrite\Model\ObjectRegistry
     * @since 2.0.0
     */
    protected $productCategories;

    /**
     * @deprecated 2.2.0
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var ProductScopeRewriteGenerator
     * @since 2.2.0
     */
    private $productScopeRewriteGenerator;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator
     * @param \Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator
     * @param \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator $categoriesUrlRewriteGenerator
     * @param \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory $objectRegistryFactory
     * @param \Magento\CatalogUrlRewrite\Service\V1\StoreViewService $storeViewService
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @since 2.0.0
     */
    public function __construct(
        CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator,
        CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator,
        CategoriesUrlRewriteGenerator $categoriesUrlRewriteGenerator,
        ObjectRegistryFactory $objectRegistryFactory,
        StoreViewService $storeViewService,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->canonicalUrlRewriteGenerator = $canonicalUrlRewriteGenerator;
        $this->currentUrlRewritesRegenerator = $currentUrlRewritesRegenerator;
        $this->categoriesUrlRewriteGenerator = $categoriesUrlRewriteGenerator;
        $this->objectRegistryFactory = $objectRegistryFactory;
        $this->storeViewService = $storeViewService;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve Delegator for generation rewrites in different scopes
     *
     * @deprecated 2.2.0
     * @return ProductScopeRewriteGenerator|mixed
     * @since 2.2.0
     */
    private function getProductScopeRewriteGenerator()
    {
        if (!$this->productScopeRewriteGenerator) {
            $this->productScopeRewriteGenerator = ObjectManager::getInstance()
            ->get(ProductScopeRewriteGenerator::class);
        }

        return $this->productScopeRewriteGenerator;
    }

    /**
     * Generate product url rewrites
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     * @since 2.0.0
     */
    public function generate(Product $product, $rootCategoryId = null)
    {
        if ($product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE) {
            return [];
        }

        $storeId = $product->getStoreId();

        $productCategories = $product->getCategoryCollection()
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');

        $urls = $this->isGlobalScope($storeId)
            ? $this->generateForGlobalScope($productCategories, $product, $rootCategoryId)
            : $this->generateForSpecificStoreView($storeId, $productCategories, $product, $rootCategoryId);

        return $urls;
    }

    /**
     * Check is global scope
     *
     * @deprecated 2.2.0
     * @param int|null $storeId
     * @return bool
     * @since 2.0.0
     */
    protected function isGlobalScope($storeId)
    {
        return $this->getProductScopeRewriteGenerator()->isGlobalScope($storeId);
    }

    /**
     * Generate list of urls for global scope
     *
     * @deprecated 2.2.0
     * @param \Magento\Framework\Data\Collection $productCategories
     * @param \Magento\Catalog\Model\Product|null $product
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     * @since 2.0.0
     */
    protected function generateForGlobalScope($productCategories, $product = null, $rootCategoryId = null)
    {
        return $this->getProductScopeRewriteGenerator()->generateForGlobalScope(
            $productCategories,
            $product,
            $rootCategoryId
        );
    }

    /**
     * Generate list of urls for specific store view
     *
     * @deprecated 2.2.0
     * @param int $storeId
     * @param \Magento\Framework\Data\Collection $productCategories
     * @param Product|null $product
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     * @since 2.0.0
     */
    protected function generateForSpecificStoreView(
        $storeId,
        $productCategories,
        $product = null,
        $rootCategoryId = null
    ) {
        return $this->getProductScopeRewriteGenerator()
            ->generateForSpecificStoreView($storeId, $productCategories, $product, $rootCategoryId);
    }

    /**
     * @deprecated 2.2.0
     * @param \Magento\Catalog\Model\Category $category
     * @param int $storeId
     * @return bool
     * @since 2.0.0
     */
    protected function isCategoryProperForGenerating($category, $storeId)
    {
        return $this->getProductScopeRewriteGenerator()->isCategoryProperForGenerating($category, $storeId);
    }
}
