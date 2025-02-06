<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\CatalogImportExport\Model\Import\Product\SkuStorage;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Eav\Model\ResourceModel\AttributeValue;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\ImportExport\Model\Import as ImportExport;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AfterImportDataObserver implements ObserverInterface
{
    /**
     * Url Key Attribute
     */
    public const URL_KEY_ATTRIBUTE_CODE = 'url_key';

    /**
     * @var StoreViewService
     * @deprecated No longer used.
     * @see nothing
     */
    protected $storeViewService;

    /**
     * @var Product
     * @deprecated No longer used.
     * @see nothing
     */
    protected $product;

    /**
     * @var array
     * @deprecated No longer used.
     * @see nothing
     */
    protected $productsWithStores;

    /**
     * @var array
     * @deprecated No longer used.
     * @see nothing
     */
    protected $products = [];

    /**
     * @var ObjectRegistryFactory
     */
    protected $objectRegistryFactory;

    /**
     * @var ObjectRegistry
     */
    protected $productCategories;

    /**
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var ImportProduct
     */
    protected $import;

    /**
     * @var ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var array
     */
    protected $acceptableCategories;

    /**
     * @var ProductUrlPathGenerator
     */
    protected $productUrlPathGenerator;

    /**
     * @var array
     */
    protected $websitesToStoreIds;

    /**
     * @var array
     */
    protected $storesCache = [];

    /**
     * @var array
     */
    protected $categoryCache = [];

    /**
     * @var array
     */
    protected $websiteCache = [];

    /**
     * @var array
     */
    protected $vitalForGenerationFields = [
        'sku',
        'url_key',
        'url_path',
        'name',
        'visibility',
        'save_rewrites_history',
    ];

    /**
     * @var MergeDataProvider
     */
    private $mergeDataProviderPrototype;

    /**
     * Factory for creating category collection.
     *
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * Array of invoked categories during url rewrites generation.
     *
     * @var array
     */
    private $categoriesCache = [];

    /**
     * @var ScopeConfigInterface|null
     */
    private $scopeConfig;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var AttributeValue
     */
    private $attributeValue;

    /**
     * @var null|array
     */
    private $cachedValues = null;

    /**
     * @var SkuStorage
     */
    private SkuStorage $skuStorage;

    /**
     * @param ProductFactory $catalogProductFactory
     * @param ObjectRegistryFactory $objectRegistryFactory
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param StoreViewService $storeViewService
     * @param StoreManagerInterface $storeManager
     * @param UrlPersistInterface $urlPersist
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param UrlFinderInterface $urlFinder
     * @param MergeDataProviderFactory|null $mergeDataProviderFactory
     * @param CategoryCollectionFactory|null $categoryCollectionFactory
     * @param ScopeConfigInterface|null $scopeConfig
     * @param CollectionFactory|null $collectionFactory
     * @param AttributeValue|null $attributeValue
     * @param SkuStorage|null $skuStorage
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ProductFactory $catalogProductFactory,
        ObjectRegistryFactory $objectRegistryFactory,
        ProductUrlPathGenerator $productUrlPathGenerator,
        StoreViewService $storeViewService,
        StoreManagerInterface $storeManager,
        UrlPersistInterface $urlPersist,
        UrlRewriteFactory $urlRewriteFactory,
        UrlFinderInterface $urlFinder,
        ?MergeDataProviderFactory $mergeDataProviderFactory = null,
        ?CategoryCollectionFactory $categoryCollectionFactory = null,
        ?ScopeConfigInterface $scopeConfig = null,
        ?CollectionFactory $collectionFactory = null,
        ?AttributeValue $attributeValue = null,
        ?SkuStorage $skuStorage = null
    ) {
        $this->urlPersist = $urlPersist;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->objectRegistryFactory = $objectRegistryFactory;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->storeManager = $storeManager;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlFinder = $urlFinder;
        $mergeDataProviderFactory = $mergeDataProviderFactory ?: ObjectManager::getInstance()->get(
            MergeDataProviderFactory::class
        );
        $this->mergeDataProviderPrototype = $mergeDataProviderFactory->create();
        $this->categoryCollectionFactory = $categoryCollectionFactory ?:
            ObjectManager::getInstance()->get(CategoryCollectionFactory::class);
        $this->scopeConfig = $scopeConfig ?:
            ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->productCollectionFactory = $collectionFactory ?:
            ObjectManager::getInstance()->get(CollectionFactory::class);
        $this->attributeValue = $attributeValue ?:
            ObjectManager::getInstance()->get(AttributeValue::class);
        $this->skuStorage = $skuStorage ?:
            ObjectManager::getInstance()->get(SkuStorage::class);
    }

    /**
     * Action after data import. Save new url rewrites and remove old if exist.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws UrlAlreadyExistsException
     */
    public function execute(Observer $observer)
    {
        $this->import = $observer->getEvent()->getAdapter();
        $bunch = $observer->getEvent()->getBunch();
        if (!$bunch) {
            return;
        }
        $products = $this->populateForUrlsGeneration($bunch);
        $productUrls = $this->generateUrls($products);
        if ($productUrls) {
            $this->urlPersist->replace($productUrls);
        }
    }

    /**
     * Create product models from imported data and get url_key from existing products when not in import data.
     *
     * @param array[] $bunch
     * @return Product[]
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function populateForUrlsGeneration(array $bunch) : array
    {
        $products = [];
        $productIdsMissingUrlKeyByStore = [];
        foreach ($bunch as $product) {
            $this->populateForUrlGeneration($product, $products);
        }
        foreach ($products as $productsByStore) {
            foreach ($productsByStore as $storeId => $product) {
                if (null === $product->getData('url_key')) {
                    $productIdsMissingUrlKeyByStore[$storeId][] = $product->getId();
                }
            }
        }
        foreach ($productIdsMissingUrlKeyByStore as $storeId => $productIds) {
            $this->getUrlKeyAndNameForProductsByIds($productIds, $storeId, $products);
        }
        return $products;
    }

    /**
     * Create product model from imported data for URL rewrite purposes.
     *
     * @param array $rowData
     * @param Product[] $products
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function populateForUrlGeneration(array $rowData, array &$products)
    {
        $newSku = $this->import->getNewSku($rowData[ImportProduct::COL_SKU]);
        if (!$this->isNeedToPopulateForUrlGeneration($rowData, $newSku)) {
            return null;
        }
        $rowData['entity_id'] = $newSku['entity_id'];
        $product = $this->catalogProductFactory->create();
        $product->setId($rowData['entity_id']);
        foreach ($this->vitalForGenerationFields as $field) {
            if (isset($rowData[$field])) {
                $product->setData($field, $rowData[$field]);
            }
        }
        $this->categoryCache[$rowData['entity_id']] = $this->import->getProductCategories($rowData['sku']);
        $this->websiteCache[$rowData['entity_id']] = $this->import->getProductWebsites($rowData['sku']);
        foreach ($this->websiteCache[$rowData['entity_id']] as $websiteId) {
            if (!isset($this->websitesToStoreIds[$websiteId])) {
                $this->websitesToStoreIds[$websiteId] = $this->storeManager->getWebsite($websiteId)->getStoreIds();
            }
        }
        $this->setStoreToProduct($product, $rowData);
        if ($this->isGlobalScope($product->getStoreId())) {
            $this->populateGlobalProduct($product, $products);
        } else {
            $this->storesCache[$product->getStoreId()] = true;
            $this->addProductToImport($product, $product->getStoreId(), $products);
        }
    }

    /**
     * Check is need to populate data for url generation
     *
     * @param array $rowData
     * @param array $newSku
     * @return bool
     */
    private function isNeedToPopulateForUrlGeneration($rowData, $newSku): bool
    {
        if ((
            (empty($newSku) || !isset($newSku['entity_id']))
                || ($this->import->getRowScope($rowData) == ImportProduct::SCOPE_STORE
                    && empty($rowData[self::URL_KEY_ATTRIBUTE_CODE]))
                || ($this->skuStorage->has($rowData[ImportProduct::COL_SKU] ?? '')
                    && !isset($rowData[self::URL_KEY_ATTRIBUTE_CODE])
                    && $this->import->getBehavior() === ImportExport::BEHAVIOR_APPEND)
        )
            && !isset($rowData["categories"])
        ) {
            return false;
        }
        return true;
    }

    /**
     * Add store id to product data.
     *
     * @param Product $product
     * @param array $rowData
     * @return void
     */
    private function setStoreToProduct(Product $product, array $rowData): void
    {
        if (!empty($rowData[ImportProduct::COL_STORE])
            && ($storeId = $this->import->getStoreIdByCode($rowData[ImportProduct::COL_STORE]))
        ) {
            $product->setStoreId($storeId);
        } elseif (!$product->hasData(Product::STORE_ID)) {
            $product->setStoreId(Store::DEFAULT_STORE_ID);
        }
    }

    /**
     * Add product to import
     *
     * @param Product $product
     * @param string $storeId
     * @param Product[] $products
     * @return void
     */
    private function addProductToImport(Product $product, string $storeId, array &$products) : void
    {
        if ($product->getVisibility() == (string)Visibility::getOptionArray()[Visibility::VISIBILITY_NOT_VISIBLE]) {
            return;
        }
        $products[$product->getId()][$storeId] = $product;
    }

    /**
     * Populate global product
     *
     * @param Product $product
     * @param Product[] $products
     * @return void
     */
    private function populateGlobalProduct($product, array &$products) : void
    {
        foreach ($this->import->getProductWebsites($product->getSku()) as $websiteId) {
            foreach ($this->websitesToStoreIds[$websiteId] as $storeId) {
                $this->storesCache[$storeId] = true;
                if (!$this->isGlobalScope($storeId)) {
                    $this->addProductToImport($product, $storeId, $products);
                }
            }
        }
    }

    /**
     * Generate product url rewrites
     *
     * @param Product[] $products
     * @return UrlRewrite[]
     * @throws LocalizedException
     */
    private function generateUrls(array $products)
    {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $mergeDataProvider->merge($this->canonicalUrlRewriteGenerate($products));
        if ($this->isCategoryRewritesEnabled()) {
            $mergeDataProvider->merge($this->categoriesUrlRewriteGenerate($products));
        }
        $mergeDataProvider->merge($this->currentUrlRewritesRegenerate($products));
        $this->productCategories = null;
        return $mergeDataProvider->getData();
    }

    /**
     * Check is global scope
     *
     * @param int|null $storeId
     * @return bool
     */
    private function isGlobalScope($storeId)
    {
        return null === $storeId || Store::DEFAULT_STORE_ID === (int) $storeId;
    }

    /**
     * Generate list based on store view
     *
     * @param Product[] $products
     * @return UrlRewrite[]
     */
    private function canonicalUrlRewriteGenerate(array $products)
    {
        $urls = [];
        foreach ($products as $productId => $productsByStores) {
            foreach ($productsByStores as $storeId => $product) {
                if ($this->productUrlPathGenerator->getUrlPath($product)) {
                    $reqPath = $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId);
                    $targetPath = $this->productUrlPathGenerator->getCanonicalUrlPath($product);
                    if ((int) $storeId !== (int) $product->getStoreId()
                        && $this->isGlobalScope($product->getStoreId())) {
                        $this->initializeCacheForProducts($products);
                        $reqPath = $this->getReqPath((int)$productId, (int)$storeId, $product);
                    }
                    $urls[] = $this->urlRewriteFactory->create()
                        ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                        ->setEntityId($productId)
                        ->setRequestPath($reqPath)
                        ->setTargetPath($targetPath)
                        ->setStoreId($storeId);
                }
            }
        }
        return $urls;
    }

    /**
     * Initialization for cache with scop based values
     *
     * @param array $products
     * @return void
     */
    private function initializeCacheForProducts(array $products) : void
    {
        if ($this->cachedValues === null) {
            $this->cachedValues = $this->getScopeBasedUrlKeyValues($products);
        }
    }

    /**
     * Get request path for the selected scope
     *
     * @param int $productId
     * @param int $storeId
     * @param Product $product
     * @param Category|null $category
     * @return string
     */
    private function getReqPath(int $productId, int $storeId, Product $product, ?Category $category = null) : string
    {
        $reqPath = $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category);
        if (!empty($this->cachedValues) && isset($this->cachedValues[$productId][$storeId])) {
            $storeProduct = clone $product;
            $storeProduct->setStoreId($storeId);
            $storeProduct->setUrlKey($this->cachedValues[$productId][$storeId]);
            $reqPath = $this->productUrlPathGenerator->getUrlPathWithSuffix($storeProduct, $storeId, $category);
        }
        return $reqPath;
    }

    /**
     * Get url key attribute values for the specified scope
     *
     * @param array $products
     * @return array
     */
    private function getScopeBasedUrlKeyValues(array $products) : array
    {
        $values = [];
        $productIds = [];
        $storeIds = [];
        foreach ($products as $productId => $productsByStores) {
            $productIds[] = (int) $productId;
            foreach (array_keys($productsByStores) as $id) {
                $storeIds[] = (int) $id;
            }
        }
        $productIds = array_unique($productIds);
        $storeIds = array_unique($storeIds);
        if (!empty($productIds) && !empty($storeIds)) {
            $values = $this->attributeValue->getValuesMultiple(
                ProductInterface::class,
                $productIds,
                [ProductAttributeInterface::CODE_SEO_FIELD_URL_KEY],
                $storeIds
            );
        }

        return $values;
    }

    /**
     * Generate list based on categories.
     *
     * @param Product[] $products
     * @return UrlRewrite[]
     * @throws LocalizedException
     */
    private function categoriesUrlRewriteGenerate(array $products): array
    {
        $urls = [];
        foreach ($products as $productId => $productsByStores) {
            foreach ($productsByStores as $storeId => $product) {
                foreach ($this->categoryCache[$productId] as $categoryId) {
                    $category = $this->getCategoryById($categoryId, $storeId);
                    if ($category->getParentId() == Category::TREE_ROOT_ID) {
                        continue;
                    }
                    $requestPath = $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category);
                    $targetPath = $this->productUrlPathGenerator->getCanonicalUrlPath($product, $category);
                    if ((int) $storeId !== (int) $product->getStoreId()
                        && $this->isGlobalScope($product->getStoreId())) {
                        $this->initializeCacheForProducts($products);
                        $requestPath = $this->getReqPath((int)$productId, (int)$storeId, $product, $category);
                    }
                    $urls[] = [
                            $this->urlRewriteFactory->create()
                            ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                            ->setEntityId($productId)
                            ->setRequestPath($requestPath)
                            ->setTargetPath($targetPath)
                            ->setStoreId($storeId)
                            ->setMetadata(['category_id' => $category->getId()])
                    ];
                    $parentCategoryIds = $category->getAnchorsAbove();
                    if ($parentCategoryIds) {
                        $urls[] = $this->getParentCategoriesUrlRewrites($parentCategoryIds, $storeId, $product);
                    }
                }
            }
        }
        return array_merge([], ...$urls);
    }

    /**
     * Generate list based on current rewrites
     *
     * @param Product[] $products
     * @return UrlRewrite[]
     */
    private function currentUrlRewritesRegenerate(array $products)
    {
        $currentUrlRewrites = $this->urlFinder->findAllByData(
            [
                UrlRewrite::STORE_ID => array_keys($this->storesCache),
                UrlRewrite::ENTITY_ID => array_keys($products),
                UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            ]
        );
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        foreach ($currentUrlRewrites as $currentUrlRewrite) {
            $category = $this->retrieveCategoryFromMetadata($currentUrlRewrite);
            if ($category === false) {
                continue;
            }
            $urls = $currentUrlRewrite->getIsAutogenerated()
                ? $this->generateForAutogenerated($currentUrlRewrite, $category, $products)
                : $this->generateForCustom($currentUrlRewrite, $category, $products);
            $mergeDataProvider->merge($urls);
        }
        $urlRewrites = $mergeDataProvider->getData();
        $this->productCategories = null;
        return $urlRewrites;
    }

    /**
     * Generate url-rewrite for outogenerated url-rewirte.
     *
     * @param UrlRewrite $url
     * @param Category|null $category
     * @param Product[] $products
     * @return array
     */
    private function generateForAutogenerated(UrlRewrite $url, ?Category $category, array $products) : array
    {
        $storeId = $url->getStoreId();
        $productId = $url->getEntityId();
        if (!isset($products[$productId][$storeId])) {
            return [];
        }
        $product = $products[$productId][$storeId];
        if (!$product->getData('save_rewrites_history')) {
            return [];
        }
        $targetPath = $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category);
        if ($url->getRequestPath() === $targetPath) {
            return [];
        }
        return [
            $this->urlRewriteFactory->create()
                ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                ->setEntityId($productId)
                ->setRequestPath($url->getRequestPath())
                ->setTargetPath($targetPath)
                ->setRedirectType(OptionProvider::PERMANENT)
                ->setStoreId($storeId)
                ->setDescription($url->getDescription())
                ->setIsAutogenerated(0)
                ->setMetadata($url->getMetadata())
        ];
    }

    /**
     * Generate url-rewrite for custom url-rewrite.
     *
     * @param UrlRewrite $url
     * @param Category|null $category
     * @param Product[] $products
     * @return UrlRewrite[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function generateForCustom(UrlRewrite $url, ?Category $category, array $products) : array
    {
        $storeId = $url->getStoreId();
        $productId = $url->getEntityId();
        if (isset($products[$productId][$storeId])) {
            $product = $products[$productId][$storeId];
            $targetPath = $url->getRedirectType()
                ? $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category)
                : $url->getTargetPath();
            if ((int) $storeId !== (int) $product->getStoreId()
                && $this->isGlobalScope($product->getStoreId())) {
                $this->initializeCacheForProducts($products);
                if (!empty($this->cachedValues) && isset($this->cachedValues[$productId][$storeId])) {
                    $storeProduct = clone $product;
                    $storeProduct->setStoreId($storeId);
                    $storeProduct->setUrlKey($this->cachedValues[$productId][$storeId]);
                    $targetPath = $url->getRedirectType()
                        ? $this->productUrlPathGenerator->getUrlPathWithSuffix($storeProduct, $storeId, $category)
                        : $url->getTargetPath();
                }
            }
            if ($url->getRequestPath() === $targetPath) {
                return [];
            }
            $urlRewrite = $this->urlRewriteFactory->create();
            $urlRewrite->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE);
            $urlRewrite->setEntityId($productId);
            $urlRewrite->setRequestPath($url->getRequestPath());
            $urlRewrite->setTargetPath($targetPath);
            $urlRewrite->setRedirectType($url->getRedirectType());
            $urlRewrite->setStoreId($storeId);
            $urlRewrite->setDescription($url->getDescription());
            $urlRewrite->setIsAutogenerated(0);
            $urlRewrite->setMetadata($url->getMetadata());
            return [$urlRewrite];
        }
        return [];
    }

    /**
     * Retrieve category from url metadata.
     *
     * @param UrlRewrite $url
     * @return Category|null|bool
     */
    private function retrieveCategoryFromMetadata(UrlRewrite $url)
    {
        $metadata = $url->getMetadata();
        if (isset($metadata['category_id'])) {
            $category = $this->import->getCategoryProcessor()->getCategoryById($metadata['category_id']);
            return $category === null ? false : $category;
        }
        return null;
    }

    /**
     * Get category by id considering store scope.
     *
     * @param int $categoryId
     * @param int $storeId
     * @return Category|DataObject
     * @throws LocalizedException
     */
    private function getCategoryById(int $categoryId, int $storeId)
    {
        if (!isset($this->categoriesCache[$categoryId][$storeId])) {
            /** @var CategoryCollection $categoryCollection */
            $categoryCollection = $this->categoryCollectionFactory->create();
            $categoryCollection->addIdFilter([$categoryId])
                ->setStoreId($storeId)
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('url_path');
            $this->categoriesCache[$categoryId][$storeId] = $categoryCollection->getFirstItem();
        }
        return $this->categoriesCache[$categoryId][$storeId];
    }

    /**
     * Check config value of generate_category_product_rewrites
     *
     * @return bool
     */
    private function isCategoryRewritesEnabled() : bool
    {
        return (bool)$this->scopeConfig->getValue('catalog/seo/generate_category_product_rewrites');
    }

    /**
     * Generate url-rewrite for anchor parent-categories.
     *
     * @param array $categoryIds
     * @param int $storeId
     * @param Product $product
     * @return array
     * @throws LocalizedException
     */
    private function getParentCategoriesUrlRewrites(array $categoryIds, int $storeId, Product $product): array
    {
        $urls = [];
        foreach ($categoryIds as $categoryId) {
            $category = $this->getCategoryById($categoryId, $storeId);
            if ($category->getParentId() == Category::TREE_ROOT_ID) {
                continue;
            }
            $requestPath = $this->productUrlPathGenerator
                ->getUrlPathWithSuffix($product, $storeId, $category);
            $targetPath = $this->productUrlPathGenerator
                ->getCanonicalUrlPath($product, $category);
            $urls[] = $this->urlRewriteFactory->create()
                ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                ->setEntityId($product->getId())
                ->setRequestPath($requestPath)
                ->setTargetPath($targetPath)
                ->setStoreId($storeId)
                ->setMetadata(['category_id' => $category->getId()]);
        }
        return $urls;
    }

    /**
     * Get Products' url_key and name by product Ids
     *
     * @param int[] $productIds
     * @param int $storeId
     * @param array[] $importedProducts
     * @return void
     */
    private function getUrlKeyAndNameForProductsByIds(array $productIds, int $storeId, array $importedProducts): void
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->setStoreId($storeId);
        $productCollection->addAttributeToSelect('url_key');
        $productCollection->addAttributeToSelect('name');
        $productCollection->addFieldToFilter(
            'entity_id',
            ['in' => array_unique($productIds)]
        );
        $products = $productCollection->getItems();
        foreach ($products as $product) {
            $productId = $product->getId();
            $importedProduct = $importedProducts[$productId][$storeId];
            $urlKey = $product->getUrlKey();
            if (!empty($urlKey)) {
                $importedProduct->setData('url_key', $urlKey);
                continue;
            }
            $name = $importedProduct->getName();
            if (empty($name)) {
                $name = $product->getName();
            }
            if (empty($name)) {
                continue;
            }
            $product->formatUrlKey($name);
        }
    }
}
