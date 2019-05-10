<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\CatalogUrlRewrite\Model\ObjectRegistryFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
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
use RuntimeException;

/**
 * Class AfterImportDataObserver
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AfterImportDataObserver implements ObserverInterface
{
    /**
     * Url Key Attribute
     */
    const URL_KEY_ATTRIBUTE_CODE = 'url_key';

    /**
     * @var StoreViewService
     */
    protected $storeViewService;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var array
     */
    protected $productsWithStores;

    /**
     * @var array
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
        'save_rewrites_history'
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
     * @throws RuntimeException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        MergeDataProviderFactory $mergeDataProviderFactory = null,
        CategoryCollectionFactory $categoryCollectionFactory = null,
        ScopeConfigInterface $scopeConfig = null
    ) {
        $this->urlPersist = $urlPersist;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->objectRegistryFactory = $objectRegistryFactory;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->storeViewService = $storeViewService;
        $this->storeManager = $storeManager;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlFinder = $urlFinder;
        if (!isset($mergeDataProviderFactory)) {
            $mergeDataProviderFactory = ObjectManager::getInstance()->get(MergeDataProviderFactory::class);
        }
        $this->mergeDataProviderPrototype = $mergeDataProviderFactory->create();
        $this->categoryCollectionFactory = $categoryCollectionFactory ?:
            ObjectManager::getInstance()->get(CategoryCollectionFactory::class);
        $this->scopeConfig = $scopeConfig ?:
            ObjectManager::getInstance()->get(ScopeConfigInterface::class);
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
        if ($products = $observer->getEvent()->getBunch()) {
            foreach ($products as $product) {
                $this->_populateForUrlGeneration($product);
            }
            $productUrls = $this->generateUrls();
            if ($productUrls) {
                $this->urlPersist->replace($productUrls);
            }
        }
    }

    /**
     * Create product model from imported data for URL rewrite purposes.
     *
     * @param array $rowData
     * @return AfterImportDataObserver|null
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _populateForUrlGeneration($rowData)
    {
        $newSku = $this->import->getNewSku($rowData[ImportProduct::COL_SKU]);
        if (empty($newSku) || !isset($newSku['entity_id'])) {
            return null;
        }
        if ($this->import->getRowScope($rowData) == ImportProduct::SCOPE_STORE
            && empty($rowData[self::URL_KEY_ATTRIBUTE_CODE])) {
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
            $this->populateGlobalProduct($product);
        } else {
            $this->storesCache[$product->getStoreId()] = true;
            $this->addProductToImport($product, $product->getStoreId());
        }
        return $this;
    }

    /**
     * Add store id to product data.
     *
     * @param Product $product
     * @param array $rowData
     * @return void
     */
    protected function setStoreToProduct(Product $product, array $rowData)
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
     * @return $this
     */
    protected function addProductToImport($product, $storeId)
    {
        if ($product->getVisibility() == (string)Visibility::getOptionArray()[Visibility::VISIBILITY_NOT_VISIBLE]) {
            return $this;
        }
        if (!isset($this->products[$product->getId()])) {
            $this->products[$product->getId()] = [];
        }
        $this->products[$product->getId()][$storeId] = $product;
        return $this;
    }

    /**
     * Populate global product
     *
     * @param Product $product
     * @return $this
     */
    protected function populateGlobalProduct($product)
    {
        foreach ($this->import->getProductWebsites($product->getSku()) as $websiteId) {
            foreach ($this->websitesToStoreIds[$websiteId] as $storeId) {
                $this->storesCache[$storeId] = true;
                if (!$this->isGlobalScope($storeId)) {
                    $this->addProductToImport($product, $storeId);
                }
            }
        }
        return $this;
    }

    /**
     * Generate product url rewrites
     *
     * @return UrlRewrite[]
     * @throws LocalizedException
     */
    protected function generateUrls()
    {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $mergeDataProvider->merge($this->canonicalUrlRewriteGenerate());
        if ($this->isCategoryRewritesEnabled()) {
            $mergeDataProvider->merge($this->categoriesUrlRewriteGenerate());
        }
        $mergeDataProvider->merge($this->currentUrlRewritesRegenerate());
        $this->productCategories = null;

        unset($this->products);
        $this->products = [];

        return $mergeDataProvider->getData();
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
     * Generate list based on store view
     *
     * @return UrlRewrite[]
     */
    protected function canonicalUrlRewriteGenerate()
    {
        $urls = [];
        foreach ($this->products as $productId => $productsByStores) {
            foreach ($productsByStores as $storeId => $product) {
                if ($this->productUrlPathGenerator->getUrlPath($product)) {
                    $urls[] = $this->urlRewriteFactory->create()
                        ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                        ->setEntityId($productId)
                        ->setRequestPath($this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId))
                        ->setTargetPath($this->productUrlPathGenerator->getCanonicalUrlPath($product))
                        ->setStoreId($storeId);
                }
            }
        }

        return $urls;
    }

    /**
     * Generate list based on categories.
     *
     * @return UrlRewrite[]
     * @throws LocalizedException
     */
    protected function categoriesUrlRewriteGenerate()
    {
        $urls = [];
        foreach ($this->products as $productId => $productsByStores) {
            foreach ($productsByStores as $storeId => $product) {
                foreach ($this->categoryCache[$productId] as $categoryId) {
                    $category = $this->getCategoryById($categoryId, $storeId);
                    if ($category->getParentId() == Category::TREE_ROOT_ID) {
                        continue;
                    }
                    $requestPath = $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category);
                    $urls[] = $this->urlRewriteFactory->create()
                        ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                        ->setEntityId($productId)
                        ->setRequestPath($requestPath)
                        ->setTargetPath($this->productUrlPathGenerator->getCanonicalUrlPath($product, $category))
                        ->setStoreId($storeId)
                        ->setMetadata(['category_id' => $category->getId()]);
                }
            }
        }
        return $urls;
    }

    /**
     * Generate list based on current rewrites
     *
     * @return UrlRewrite[]
     */
    protected function currentUrlRewritesRegenerate()
    {
        $currentUrlRewrites = $this->urlFinder->findAllByData(
            [
                UrlRewrite::STORE_ID => array_keys($this->storesCache),
                UrlRewrite::ENTITY_ID => array_keys($this->products),
                UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            ]
        );

        $urlRewrites = [];
        foreach ($currentUrlRewrites as $currentUrlRewrite) {
            $category = $this->retrieveCategoryFromMetadata($currentUrlRewrite);
            if ($category === false) {
                continue;
            }
            $url = $currentUrlRewrite->getIsAutogenerated()
                ? $this->generateForAutogenerated($currentUrlRewrite, $category)
                : $this->generateForCustom($currentUrlRewrite, $category);
            $urlRewrites = array_merge($urlRewrites, $url);
        }

        $this->product = null;
        $this->productCategories = null;
        return $urlRewrites;
    }

    /**
     * Generate url-rewrite for outogenerated url-rewirte.
     *
     * @param UrlRewrite $url
     * @param Category $category
     * @return array
     */
    protected function generateForAutogenerated($url, $category)
    {
        $storeId = $url->getStoreId();
        $productId = $url->getEntityId();
        if (isset($this->products[$productId][$storeId])) {
            $product = $this->products[$productId][$storeId];
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
        return [];
    }

    /**
     * Generate url-rewrite for custom url-rewirte.
     *
     * @param UrlRewrite $url
     * @param Category $category
     * @return array
     */
    protected function generateForCustom($url, $category)
    {
        $storeId = $url->getStoreId();
        $productId = $url->getEntityId();
        if (isset($this->products[$productId][$storeId])) {
            $product = $this->products[$productId][$storeId];
            $targetPath = $url->getRedirectType()
                ? $this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId, $category)
                : $url->getTargetPath();
            if ($url->getRequestPath() === $targetPath) {
                return [];
            }
            return [
                $this->urlRewriteFactory->create()
                    ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                    ->setEntityId($productId)
                    ->setRequestPath($url->getRequestPath())
                    ->setTargetPath($targetPath)
                    ->setRedirectType($url->getRedirectType())
                    ->setStoreId($storeId)
                    ->setDescription($url->getDescription())
                    ->setIsAutogenerated(0)
                    ->setMetadata($url->getMetadata())
            ];
        }
        return [];
    }

    /**
     * Retrieve category from url metadata.
     *
     * @param UrlRewrite $url
     * @return Category|null|bool
     */
    protected function retrieveCategoryFromMetadata($url)
    {
        $metadata = $url->getMetadata();
        if (isset($metadata['category_id'])) {
            $category = $this->import->getCategoryProcessor()->getCategoryById($metadata['category_id']);
            return $category === null ? false : $category;
        }
        return null;
    }

    /**
     * Check, category suited for url-rewrite generation.
     *
     * @param Category $category
     * @param int $storeId
     * @return bool
     * @throws NoSuchEntityException
     */
    protected function isCategoryProperForGenerating($category, $storeId)
    {
        if (isset($this->acceptableCategories[$storeId]) &&
            isset($this->acceptableCategories[$storeId][$category->getId()])) {
            return $this->acceptableCategories[$storeId][$category->getId()];
        }
        $acceptable = false;
        if ($category->getParentId() != Category::TREE_ROOT_ID) {
            list(, $rootCategoryId) = $category->getParentIds();
            $acceptable = ($rootCategoryId == $this->storeManager->getStore($storeId)->getRootCategoryId());
        }
        if (!isset($this->acceptableCategories[$storeId])) {
            $this->acceptableCategories[$storeId] = [];
        }
        $this->acceptableCategories[$storeId][$category->getId()] = $acceptable;
        return $acceptable;
    }

    /**
     * Get category by id considering store scope.
     *
     * @param int $categoryId
     * @param int $storeId
     * @return Category|DataObject
     * @throws LocalizedException
     */
    private function getCategoryById($categoryId, $storeId)
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
    private function isCategoryRewritesEnabled()
    {
        return (bool)$this->scopeConfig->getValue('catalog/seo/generate_category_product_rewrites');
    }
}
