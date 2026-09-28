<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
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
    public const WEBSITE_URL_REWRITE_SCOPE = 'website';

    public const STORE_VIEW_URL_REWRITE_SCOPE = 'store_view';

    public const URL_REWRITE_SCOPE_CONFIG_PATH = 'catalog/seo/product_rewrite_context';

    /**
     * @var StoreViewService
     */
    private StoreViewService $storeViewService;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $config;

    /**
     * @var ObjectRegistryFactory
     */
    private ObjectRegistryFactory $objectRegistryFactory;

    /**
     * @var AnchorUrlRewriteGenerator
     */
    private AnchorUrlRewriteGenerator $anchorUrlRewriteGenerator;

    /**
     * @var CurrentUrlRewritesRegenerator
     */
    private CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator;

    /**
     * @var CategoriesUrlRewriteGenerator
     */
    private CategoriesUrlRewriteGenerator $categoriesUrlRewriteGenerator;

    /**
     * @var CanonicalUrlRewriteGenerator
     */
    private CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator;

    /**
     * @var MergeDataProvider
     */
    private MergeDataProvider $mergeDataProviderPrototype;

    /**
     * @var CategoryRepositoryInterface
     */
    private CategoryRepositoryInterface $categoryRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var GetVisibleForStores|mixed
     */
    private mixed $visibleForStores;

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
     * @param ProductRepositoryInterface|null $productRepository
     * @param GetVisibleForStores|null $visibleForStores
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
        ?MergeDataProviderFactory $mergeDataProviderFactory = null,
        ?CategoryRepositoryInterface $categoryRepository = null,
        ?ScopeConfigInterface $config = null,
        ?ProductRepositoryInterface $productRepository = null,
        ?GetVisibleForStores $visibleForStores = null
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
        $this->productRepository = $productRepository ?:
            ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $this->visibleForStores = $visibleForStores ??
            ObjectManager::getInstance()->get(GetVisibleForStores::class);
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
     * @param Collection|\Magento\Catalog\Model\Category[] $productCategories
     * @param Product $product
     * @param int|null $rootCategoryId
     * @return array
     */
    public function generateForGlobalScope($productCategories, Product $product, $rootCategoryId = null)
    {
        $productId = $product->getEntityId();
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $visibleForStores = $this->visibleForStores->execute($product);

        foreach ($product->getStoreIds() as $id) {
            if (!$this->isGlobalScope($id)) {
                if (!$this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore(
                    $id,
                    $productId,
                    Product::ENTITY
                )) {
                    if (count($visibleForStores) === 0 || in_array((int)$id, $visibleForStores)) {
                        $mergeDataProvider->merge(
                            $this->generateForSpecificStoreView(
                                $id,
                                $productCategories,
                                $product,
                                $rootCategoryId,
                                true
                            )
                        );
                    }
                } else {
                    if (count($visibleForStores) === 0 || in_array((int)$id, $visibleForStores)) {
                        $scopedProduct = $this->productRepository->getById($productId, false, $id);
                        $mergeDataProvider->merge(
                            $this->generateForSpecificStoreView(
                                $id,
                                $productCategories,
                                $scopedProduct,
                                $rootCategoryId,
                                true
                            )
                        );
                    }
                }
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
     * @param bool $isGlobalScope
     * @return UrlRewrite[]
     * @throws NoSuchEntityException
     */
    public function generateForSpecificStoreView(
        $storeId,
        $productCategories,
        Product $product,
        $rootCategoryId = null,
        bool $isGlobalScope = false
    ): array {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $mergeDataProvider->merge(array_merge(...$this->generateCanonicalUrls($product, $storeId)));

        $categories = [];
        if ($this->isCategoryRewritesEnabled()) {
            foreach ($productCategories as $category) {
                if (!$this->isCategoryProperForGenerating($category, $storeId)) {
                    continue;
                }

                $categories[] = $this->getCategoryWithOverriddenUrlKey($storeId, $category);
            }
        }
        $productCategories = $this->objectRegistryFactory->create(['entities' => $categories]);

        if ($isGlobalScope) {
            $generatedUrls = $this->generateCategoryUrls((int) $storeId, $product, $productCategories);
        } else {
            $generatedUrls = $this->generateCategoryUrlsInStoreGroup((int) $storeId, $product, $productCategories);
        }

        $mergeDataProvider->merge(array_merge(...$generatedUrls));
        $mergeDataProvider->merge(
            $this->currentUrlRewritesRegenerator->generateAnchor(
                $storeId,
                $product,
                $productCategories,
                $rootCategoryId
            )
        );
        $mergeDataProvider->merge(
            $this->currentUrlRewritesRegenerator->generate(
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
     * Generate product canonical URL in website or store view scope
     *
     * @param Product $product
     * @param mixed $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    private function generateCanonicalUrls(Product $product, mixed $storeId): array
    {
        $urls = [];
        if ($this->config->getValue(self::URL_REWRITE_SCOPE_CONFIG_PATH) === self::WEBSITE_URL_REWRITE_SCOPE) {
            $currentStore = $this->storeManager->getStore($storeId);
            $currentGroupId = $currentStore->getStoreGroupId();
            $storeList = $this->storeManager->getStores();
            foreach ($storeList as $store) {
                if ($store->getStoreGroupId() === $currentGroupId) {
                    $urls[] = $this->canonicalUrlRewriteGenerator->generate($store->getId(), $product);
                }
            }
        } else {
            $urls[] = $this->canonicalUrlRewriteGenerator->generate($storeId, $product);
        }
        return $urls;
    }

    /**
     * Generate category URLs for the whole store group.
     *
     * @param int $storeId
     * @param Product $product
     * @param ObjectRegistry $productCategories
     * @return array
     * @throws NoSuchEntityException
     */
    private function generateCategoryUrlsInStoreGroup(
        int $storeId,
        Product $product,
        ObjectRegistry $productCategories
    ): array {
        $generatedUrls = [];
        if ($this->config->getValue(self::URL_REWRITE_SCOPE_CONFIG_PATH) === self::WEBSITE_URL_REWRITE_SCOPE) {
            $currentStore = $this->storeManager->getStore($storeId);
            $currentGroupId = $currentStore->getStoreGroupId();
            $storeList = $this->storeManager->getStores();

            foreach ($storeList as $store) {
                if ($store->getStoreGroupId() === $currentGroupId && $this->isCategoryRewritesEnabled()) {
                    $groupStoreId = (int)$store->getId();
                    $generatedUrls[] = $this->generateCategoryUrls(
                        $groupStoreId,
                        $product,
                        $productCategories
                    );
                }
            }
        } else {
            $generatedUrls[] = $this->generateCategoryUrls($storeId, $product, $productCategories);
        }

        return array_merge(...$generatedUrls);
    }

    /**
     * Generate category URLs.
     *
     * @param int $storeId
     * @param Product $product
     * @param ObjectRegistry $categories
     *
     * @return array
     */
    private function generateCategoryUrls(int $storeId, Product $product, ObjectRegistry $categories): array
    {
        $generatedUrls[] = $this->categoriesUrlRewriteGenerator->generate(
            $storeId,
            $product,
            $categories
        );
        $generatedUrls[] = $this->anchorUrlRewriteGenerator->generate(
            $storeId,
            $product,
            $categories
        );

        return $generatedUrls;
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
    private function isCategoryRewritesEnabled(): bool
    {
        return (bool)$this->config->getValue('catalog/seo/generate_category_product_rewrites');
    }
}
