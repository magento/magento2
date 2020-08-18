<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Url rewrite generator for product
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductUrlRewriteGenerator
{
    public const ENTITY_TYPE = 'product';

    /**
     * @deprecated 100.1.0
     * @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService
     */
    protected $storeViewService;

    /**
     * @var \Magento\Catalog\Model\Product
     * @deprecated 100.1.0
     */
    protected $product;

    /**
     * @deprecated 100.1.0
     * @var \Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator
     */
    protected $currentUrlRewritesRegenerator;

    /**
     * @deprecated 100.1.0
     * @var \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator
     */
    protected $categoriesUrlRewriteGenerator;

    /**
     * @deprecated 100.1.0
     * @var \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator
     */
    protected $canonicalUrlRewriteGenerator;

    /**
     * @deprecated 100.1.0
     * @var \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory
     */
    protected $objectRegistryFactory;

    /**
     * @deprecated 100.1.0
     * @var \Magento\CatalogUrlRewrite\Model\ObjectRegistry
     */
    protected $productCategories;

    /**
     * @deprecated 100.1.0
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductScopeRewriteGenerator
     */
    private $productScopeRewriteGenerator;

    /**
     * @param CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator
     * @param CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator
     * @param CategoriesUrlRewriteGenerator $categoriesUrlRewriteGenerator
     * @param ObjectRegistryFactory $objectRegistryFactory
     * @param StoreViewService $storeViewService
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator,
        CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator,
        CategoriesUrlRewriteGenerator $categoriesUrlRewriteGenerator,
        ObjectRegistryFactory $objectRegistryFactory,
        StoreViewService $storeViewService,
        StoreManagerInterface $storeManager
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
     * @deprecated 100.1.4
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
     * Generate product url rewrites
     *
     * @param Product $product
     * @param int|null $rootCategoryId
     * @return UrlRewrite[]
     */
    public function generate(Product $product, $rootCategoryId = null)
    {
        $storeId = $product->getStoreId();

        $productCategories = $product->getCategoryCollection()
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');

        return $this->isGlobalScope($storeId)
            ? $this->generateForGlobalScope($productCategories, $product, $rootCategoryId)
            : $this->generateForSpecificStoreView($storeId, $productCategories, $product, $rootCategoryId);
    }

    /**
     * Check is global scope
     *
     * @deprecated 100.1.4
     * @param int|null $storeId
     * @return bool
     */
    protected function isGlobalScope($storeId)
    {
        return $this->getProductScopeRewriteGenerator()->isGlobalScope($storeId);
    }

    /**
     * Generate list of urls for global scope
     *
     * @param Collection $productCategories
     * @param Product|null $product
     * @param int|null $rootCategoryId
     * @return UrlRewrite[]
     * @deprecated 100.1.4
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
     * @deprecated 100.1.4
     * @param int $storeId
     * @param Collection $productCategories
     * @param Product|null $product
     * @param int|null $rootCategoryId
     * @return UrlRewrite[]
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
     * Is a proper category for generating
     *
     * @deprecated 100.1.4
     * @param Category $category
     * @param int $storeId
     * @return bool
     */
    protected function isCategoryProperForGenerating($category, $storeId)
    {
        return $this->getProductScopeRewriteGenerator()->isCategoryProperForGenerating($category, $storeId);
    }
}
