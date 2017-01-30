<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator;
use Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Store\Model\Store;
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

    /** @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService */
    protected $storeViewService;

    /** @var \Magento\Catalog\Model\Product */
    protected $product;

    /** @var \Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator */
    protected $currentUrlRewritesRegenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator */
    protected $categoriesUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator */
    protected $canonicalUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory */
    protected $objectRegistryFactory;

    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistry */
    protected $productCategories;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var AnchorUrlRewriteGenerator */
    private $anchorUrlRewriteGenerator;

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
     * @return AnchorUrlRewriteGenerator
     *
     * @deprecated
     */
    private function getAnchorUrlRewriteGenerator()
    {
        if ($this->anchorUrlRewriteGenerator === null) {
            $this->anchorUrlRewriteGenerator = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator');
        }
        return $this->anchorUrlRewriteGenerator;
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
     * @param int|null $storeId
     * @return bool
     */
    protected function isGlobalScope($storeId)
    {
        return null === $storeId || $storeId == Store::DEFAULT_STORE_ID;
    }

    /**
     * Generate list of urls for global scope
     *
     * @param \Magento\Framework\Data\Collection $productCategories
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForGlobalScope($productCategories)
    {
        $urls = [];
        $productId = $this->product->getEntityId();
        foreach ($this->product->getStoreIds() as $id) {
            if (!$this->isGlobalScope($id)
                && !$this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore($id, $productId, Product::ENTITY)
            ) {
                $urls = array_merge($urls, $this->generateForSpecificStoreView($id, $productCategories));
            }
        }
        return $urls;
    }

    /**
     * Generate list of urls for specific store view
     *
     * @param int $storeId
     * @param \Magento\Framework\Data\Collection $productCategories
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForSpecificStoreView($storeId, $productCategories)
    {
        $categories = [];
        foreach ($productCategories as $category) {
            if ($this->isCategoryProperForGenerating($category, $storeId)) {
                $categories[] = $category;
            }
        }
        $this->productCategories = $this->objectRegistryFactory->create(['entities' => $categories]);
        /**
         * @var $urls \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
         */
        $urls = array_merge(
            $this->canonicalUrlRewriteGenerator->generate($storeId, $this->product),
            $this->categoriesUrlRewriteGenerator->generate($storeId, $this->product, $this->productCategories),
            $this->currentUrlRewritesRegenerator->generate($storeId, $this->product, $this->productCategories),
            $this->getAnchorUrlRewriteGenerator()->generate($storeId, $this->product, $this->productCategories)
        );

        /* Reduce duplicates. Last wins */
        $result = [];
        foreach ($urls as $url) {
            $result[$url->getTargetPath() . '-' . $url->getStoreId()] = $url;
        }
        $this->productCategories = null;
        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param int $storeId
     * @return bool
     */
    protected function isCategoryProperForGenerating($category, $storeId)
    {
        if ($category->getParentId() != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            list(, $rootCategoryId) = $category->getParentIds();
            return $rootCategoryId == $this->storeManager->getStore($storeId)->getRootCategoryId();
        }
        return false;
    }
}
