<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @deprecated
     * @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService
     */
    protected $storeViewService;

    /** @var \Magento\Catalog\Model\Product */
    protected $product;

    /**
     * @deprecated
     * @var \Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator
     */
    protected $currentUrlRewritesRegenerator;

    /**
     * @deprecated
     * @var \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator
     */
    protected $categoriesUrlRewriteGenerator;

    /**
     * @deprecated
     * @var \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator
     */
    protected $canonicalUrlRewriteGenerator;

    /**
     * @deprecated
     * @var \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory
     */
    protected $objectRegistryFactory;

    /**
     * @deprecated
     * @var \Magento\CatalogUrlRewrite\Model\ObjectRegistry
     */
    protected $productCategories;

    /**
     * @deprecated
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
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
     * Retrieve Delegator for generation rewrites in different scopes
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
     * Generate product url rewrites
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function generate(Product $product)
    {
        if ($product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE) {
            return [];
        }

        $this->product = $product;
        $storeId = $this->product->getStoreId();

        $productCategories = $product->getCategoryCollection()
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');

        $urls = $this->isGlobalScope($storeId)
            ? $this->generateForGlobalScope($productCategories)
            : $this->generateForSpecificStoreView($storeId, $productCategories);

        $this->product = null;
        return $urls;
    }

    /**
     * Check is global scope
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
     * Generate list of urls for global scope
     *
     * @deprecated
     * @param \Magento\Framework\Data\Collection $productCategories
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForGlobalScope($productCategories)
    {
        return $this->getProductScopeRewriteGenerator()->generateForGlobalScope($productCategories, $this->product);
    }

    /**
     * Generate list of urls for specific store view
     *
     * @deprecated
     * @param int $storeId
     * @param \Magento\Framework\Data\Collection $productCategories
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForSpecificStoreView($storeId, $productCategories)
    {
        return $this->getProductScopeRewriteGenerator()
            ->generateForSpecificStoreView($storeId, $productCategories, $this->product);
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
