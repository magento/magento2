<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\Product\AnchorUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CategoriesUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Product\CurrentUrlRewritesRegenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Generates Product/Category URLs for different scopes
 *
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
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var ObjectRegistryFactory
     */
    private $objectRegistryFactory;

    /**
     * @var AnchorUrlRewriteGenerator
     */
    private $anchorUrlRewriteGenerator;

    /**
     * @var CurrentUrlRewritesRegenerator
     */
    private $currentUrlRewritesRegenerator;

    /**
     * @var CategoriesUrlRewriteGenerator
     */
    private $categoriesUrlRewriteGenerator;

    /**
     * @var CanonicalUrlRewriteGenerator
     */
    private $canonicalUrlRewriteGenerator;

    /**
     * @var MergeDataProvider
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
     * @param MergeDataProviderFactory|null $mergeDataProviderFactory
     * @param CategoryRepositoryInterface|null $categoryRepository
     * @param ScopeConfigInterface|null $config
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        CategoryRepositoryInterface $categoryRepository = null,
        ScopeConfigInterface $config = null
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
        $this->config = $config ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
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
     * @param Collection|Category[] $productCategories
     * @param Product $product
     * @param int|null $rootCategoryId
     * @return array
     */
    public function generateForGlobalScope($productCategories, Product $product, $rootCategoryId = null)
    {
        $productId = $product->getEntityId();
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;

        $categoriesRemoved = false;
        $assignedCategoriesStoreIds = [];
        if ($this->isCategoryRewritesEnabled()) {
            $oldCategoryIds = $product->getOrigData('category_ids');
            $categoriesRemoved = $oldCategoryIds !== null && array_diff($oldCategoryIds, $product->getCategoryIds());
            $assignedCategoriesStoreIds = $this->getAddedCategoriesStoreIds($product);
        }
        foreach ($product->getStoreIds() as $id) {
            if ($categoriesRemoved || in_array((int)$id, $assignedCategoriesStoreIds)
                || (!$this->isGlobalScope($id) && !$this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore(
                    $id,
                    $productId,
                    Product::ENTITY
                ))) {
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
     * @param Collection|Category[] $productCategories
     * @param Product $product
     * @param int|null $rootCategoryId
     * @return UrlRewrite[]
     */
    public function generateForSpecificStoreView($storeId, $productCategories, Product $product, $rootCategoryId = null)
    {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $categories = [];
        foreach ($productCategories as $category) {
            if (!$this->isCategoryProperForGenerating($category, $storeId)) {
                continue;
            }

            $categories[] = $this->getCategoryWithOverriddenUrlKey($storeId, $category);
        }

        $productCategories = $this->objectRegistryFactory->create(['entities' => $categories]);

        $mergeDataProvider->merge(
            $this->canonicalUrlRewriteGenerator->generate($storeId, $product)
        );

        if ($this->isCategoryRewritesEnabled()) {
            $mergeDataProvider->merge(
                $this->categoriesUrlRewriteGenerator->generate($storeId, $product, $productCategories)
            );
        }

        $mergeDataProvider->merge(
            $this->currentUrlRewritesRegenerator->generate(
                $storeId,
                $product,
                $productCategories,
                $rootCategoryId
            )
        );

        if ($this->isCategoryRewritesEnabled()) {
            $mergeDataProvider->merge(
                $this->anchorUrlRewriteGenerator->generate($storeId, $product, $productCategories)
            );
        }

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
     * @param Category $category
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
     * Check if URL key has been changed
     *
     * Checks if URL key has been changed for provided category and returns reloaded category,
     * in other case - returns provided category.
     *
     * Category should be loaded per appropriate store at all times. This is because whilst the URL key on the
     * category in focus might be unchanged, parent category URL keys might be. If the category store ID
     * and passed store ID are the same then return current category as it is correct but may have changed in memory
     *
     * @param int $storeId
     * @param Category $category
     *
     * @return CategoryInterface
     * @throws NoSuchEntityException
     */
    private function getCategoryWithOverriddenUrlKey($storeId, Category $category)
    {
        $isUrlKeyOverridden = $this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore(
            $storeId,
            $category->getEntityId(),
            Category::ENTITY
        );

        if (!$isUrlKeyOverridden && $storeId === $category->getStoreId()) {
            return $category;
        }

        return $this->categoryRepository->get($category->getEntityId(), $storeId);
    }

    /**
     * Check config value of generate_category_product_rewrites
     *
     * @return bool
     */
    private function isCategoryRewritesEnabled()
    {
        return (bool)$this->config->getValue('catalog/seo/generate_category_product_rewrites');
    }

    /**
     * Retrieve affected store ids for added categories
     *
     * @param Product $product
     * @return int[]
     */
    private function getAddedCategoriesStoreIds(Product $product): array
    {
        $result = [];
        $addedCategoryIds = $product->getOrigData('category_ids') === null
            ? $product->getCategoryIds()
            : array_diff($product->getCategoryIds(), $product->getOrigData('category_ids'));
        if ($addedCategoryIds) {
            $storeIds = [];
            foreach ($this->getNewCategories($product) as $category) {
                $storeIds[] = $category->getStoreIds();
            }
            $result = array_merge([], ...$storeIds);
        }

        return array_map('intval', array_unique($result));
    }

    /**
     * Return new category items from product category collection
     *
     * @param Product $product
     * @return Category[]
     */
    private function getNewCategories(Product $product): array
    {
        if ($product->getOrigData('category_ids') === null) {
            return $product->getCategoryCollection()->getItems();
        }
        return array_filter(
            $product->getCategoryCollection()->getItems(),
            function (CategoryInterface $category) use ($product) {
                return !in_array($category->getId(), $product->getOrigData('category_ids'));
            }
        );
    }
}
