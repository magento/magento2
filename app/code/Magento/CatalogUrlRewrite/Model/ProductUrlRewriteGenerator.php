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
 */
class ProductUrlRewriteGenerator
{
    /**
     * Entity type code
     */
    const ENTITY_TYPE = 'product';

    /**
     * @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService
     * @deprecated
     */
    protected $storeViewService;

    /**
     * @var \Magento\Catalog\Model\Product
     * @deprecated
     */
    protected $product;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator
     * @deprecated
     */
    protected $currentUrlRewritesRegenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator
     * @deprecated
     */
    protected $categoriesUrlRewriteGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator
     * @deprecated
     */
    protected $canonicalUrlRewriteGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory
     * @deprecated
     */
    protected $objectRegistryFactory;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\ObjectRegistry
     * @deprecated
     */
    protected $productCategories;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @deprecated
     */
    protected $storeManager;

    /**
     * Generates url rewrites for different scopes.
     *
     * @var ProductScopeRewriteGenerator
     */
    private $productScopeRewriteGenerator;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator
     * @param \Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator
     * @param \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator $categoriesUrlRewriteGenerator
     * @param \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory $objectRegistryFactory
     * @param \Magento\CatalogUrlRewrite\Service\V1\StoreViewService $storeViewService
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
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
     * Retrieve Delegator for generation rewrites in different scopes.
     *
     * @deprecated
     * @return ProductScopeRewriteGenerator|mixed
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
     * Generate product url rewrites.
     *
     * @param Product $product
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
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
     * Check is global scope.
     *
     * @deprecated
     * @param int|null $storeId
     * @return bool
     */
    protected function isGlobalScope($storeId)
    {
        return $this->getProductScopeRewriteGenerator()->isGlobalScope($storeId);
    }

    /**
     * Generate list of urls for global scope.
     *
     * @deprecated
     * @param \Magento\Framework\Data\Collection $productCategories
     * @param Product|null $product
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
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
     * Generate list of urls for specific store view.
     *
     * @deprecated
     * @param int $storeId
     * @param \Magento\Framework\Data\Collection $productCategories
     * @param Product|null $product
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
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
     * @deprecated
     * @param \Magento\Catalog\Model\Category $category
     * @param int $storeId
     * @return bool
     */
    protected function isCategoryProperForGenerating($category, $storeId)
    {
        return $this->getProductScopeRewriteGenerator()->isCategoryProperForGenerating($category, $storeId);
    }
}
