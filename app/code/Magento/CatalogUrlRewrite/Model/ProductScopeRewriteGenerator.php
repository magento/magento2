<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;

/**
 * Class ProductScopeRewriteGenerator
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductScopeRewriteGenerator
{
    /**
     * @var StoreViewService
     */
    private $storeViewService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ObjectRegistryFactory
     */
    private $objectRegistryFactory;

    /**
     * @var AnchorUrlRewriteGenerator
     */
    private $anchorUrlRewriteGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator
     */
    private $currentUrlRewritesRegenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator
     */
    private $categoriesUrlRewriteGenerator;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator
     */
    private $canonicalUrlRewriteGenerator;

    /**
     * @var \Magento\UrlRewrite\Model\MergeDataProvider
     */
    private $mergeDataProviderPrototype;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param StoreViewService $storeViewService
     * @param StoreManagerInterface $storeManager
     * @param ObjectRegistryFactory $objectRegistryFactory
     * @param CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator
     * @param CategoriesUrlRewriteGenerator $categoriesUrlRewriteGenerator
     * @param CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator
     * @param AnchorUrlRewriteGenerator $anchorUrlRewriteGenerator
     * @param \Magento\UrlRewrite\Model\MergeDataProviderFactory|null $mergeDataProviderFactory
     * @param CategoryRepositoryInterface|null $categoryRepository
     */
    public function __construct(
        StoreViewService $storeViewService,
        StoreManagerInterface $storeManager,
        ObjectRegistryFactory $objectRegistryFactory,
        CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator,
        CategoriesUrlRewriteGenerator $categoriesUrlRewriteGenerator,
        CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator,
        AnchorUrlRewriteGenerator $anchorUrlRewriteGenerator,
        MergeDataProviderFactory $mergeDataProviderFactory = null,
        CategoryRepositoryInterface $categoryRepository = null
    ) {
        $this->storeViewService = $storeViewService;
        $this->storeManager = $storeManager;
        $this->objectRegistryFactory = $objectRegistryFactory;
        $this->canonicalUrlRewriteGenerator = $canonicalUrlRewriteGenerator;
        $this->categoriesUrlRewriteGenerator = $categoriesUrlRewriteGenerator;
        $this->currentUrlRewritesRegenerator = $currentUrlRewritesRegenerator;
        $this->anchorUrlRewriteGenerator = $anchorUrlRewriteGenerator;
        if (!isset($mergeDataProviderFactory)) {
            $mergeDataProviderFactory = ObjectManager::getInstance()->get(MergeDataProviderFactory::class);
        }
        $this->mergeDataProviderPrototype = $mergeDataProviderFactory->create();
        $this->categoryRepository = $categoryRepository ?:
            ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
    }

    /**
     * Check is global scope
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isGlobalScope($storeId)
    {
        return null === $storeId || $storeId == Store::DEFAULT_STORE_ID;
    }

    /**
     * Generate url rewrites for global scope
     *
     * @param \Magento\Framework\Data\Collection|\Magento\Catalog\Model\Category[] $productCategories
     * @param Product $product
     * @param int|null $rootCategoryId
     * @return array
     */
    public function generateForGlobalScope($productCategories, Product $product, $rootCategoryId = null)
    {
        $productId = $product->getEntityId();
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;

        foreach ($product->getStoreIds() as $id) {
            if (!$this->isGlobalScope($id) &&
                !$this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore(
                    $id,
                    $productId,
                    Product::ENTITY
                )) {
                $mergeDataProvider->merge(
                    $this->generateForSpecificStoreView($id, $productCategories, $product, $rootCategoryId)
                );
            }
        }

        return $mergeDataProvider->getData();
    }

    /**
     * Generate list of urls for specific store view
     *
     * @param int $storeId
     * @param \Magento\Framework\Data\Collection|Category[] $productCategories
     * @param \Magento\Catalog\Model\Product $product
     * @param int|null $rootCategoryId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function generateForSpecificStoreView($storeId, $productCategories, Product $product, $rootCategoryId = null)
    {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $categories = [];
        foreach ($productCategories as $category) {
            if (!$this->isCategoryProperForGenerating($category, $storeId)) {
                continue;
            }

            // category should be loaded per appropriate store if category's URL key has been changed
            $categories[] = $this->getCategoryWithOverriddenUrlKey($storeId, $category);
        }

        $productCategories = $this->objectRegistryFactory->create(['entities' => $categories]);

        $mergeDataProvider->merge(
            $this->canonicalUrlRewriteGenerator->generate($storeId, $product)
        );
        $mergeDataProvider->merge(
            $this->categoriesUrlRewriteGenerator->generate($storeId, $product, $productCategories)
        );
        $mergeDataProvider->merge(
            $this->currentUrlRewritesRegenerator->generate(
                $storeId,
                $product,
                $productCategories,
                $rootCategoryId
            )
        );
        $mergeDataProvider->merge(
            $this->anchorUrlRewriteGenerator->generate($storeId, $product, $productCategories)
        );
        $mergeDataProvider->merge(
            $this->currentUrlRewritesRegenerator->generateAnchor(
                $storeId,
                $product,
                $productCategories,
                $rootCategoryId
            )
        );
        return $mergeDataProvider->getData();
    }

    /**
     * Check possibility for url rewrite generation
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param int $storeId
     * @return bool
     */
    public function isCategoryProperForGenerating(Category $category, $storeId)
    {
        $parentIds = $category->getParentIds();
        if (is_array($parentIds) && count($parentIds) >= 2) {
            $rootCategoryId = $parentIds[1];
            return $rootCategoryId == $this->storeManager->getStore($storeId)->getRootCategoryId();
        }
        return false;
    }

    /**
     * Checks if URL key has been changed for provided category and returns reloaded category,
     * in other case - returns provided category.
     *
     * @param $storeId
     * @param Category $category
     * @return Category
     */
    private function getCategoryWithOverriddenUrlKey($storeId, Category $category)
    {
        $isUrlKeyOverridden = $this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore(
            $storeId,
            $category->getEntityId(),
            Category::ENTITY
        );

        if (!$isUrlKeyOverridden) {
            return $category;
        }
        return $this->categoryRepository->get($category->getEntityId(), $storeId);
    }
}
