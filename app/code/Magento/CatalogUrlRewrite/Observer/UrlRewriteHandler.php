<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class for management url rewrites.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UrlRewriteHandler
{
    /**
     * @var ChildrenCategoriesProvider
     */
    protected $childrenCategoriesProvider;

    /**
     * @var CategoryUrlRewriteGenerator
     */
    protected $categoryUrlRewriteGenerator;

    /**
     * @var ProductUrlRewriteGenerator
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var array
     */
    protected $isSkippedProduct;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var CategoryProductUrlPathGenerator
     */
    private $categoryBasedProductRewriteGenerator;

    /**
     * @var MergeDataProvider
     */
    private $mergeDataProviderPrototype;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var ProductScopeRewriteGenerator
     */
    private $productScopeRewriteGenerator;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param CollectionFactory $productCollectionFactory
     * @param CategoryProductUrlPathGenerator $categoryBasedProductRewriteGenerator
     * @param MergeDataProviderFactory|null $mergeDataProviderFactory
     * @param Json|null $serializer
     * @param ProductScopeRewriteGenerator|null $productScopeRewriteGenerator
     * @param ScopeConfigInterface|null $scopeConfig
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ChildrenCategoriesProvider $childrenCategoriesProvider,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        CollectionFactory $productCollectionFactory,
        CategoryProductUrlPathGenerator $categoryBasedProductRewriteGenerator,
        MergeDataProviderFactory $mergeDataProviderFactory = null,
        Json $serializer = null,
        ProductScopeRewriteGenerator $productScopeRewriteGenerator = null,
        ScopeConfigInterface $scopeConfig = null
    ) {
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryBasedProductRewriteGenerator = $categoryBasedProductRewriteGenerator;

        $mergeDataProviderFactory = $mergeDataProviderFactory
            ?? ObjectManager::getInstance()->get(MergeDataProviderFactory::class);
        $this->mergeDataProviderPrototype = $mergeDataProviderFactory->create();
        $this->serializer = $serializer
            ?? ObjectManager::getInstance()->get(Json::class);
        $this->productScopeRewriteGenerator = $productScopeRewriteGenerator
            ?? ObjectManager::getInstance()->get(ProductScopeRewriteGenerator::class);
        $this->scopeConfig = $scopeConfig
            ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * Generates URL rewrites for products assigned to category.
     *
     * @param Category $category
     * @return array
     */
    public function generateProductUrlRewrites(Category $category): array
    {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $this->isSkippedProduct[$category->getEntityId()] = [];
        $saveRewriteHistory = (bool)$category->getData('save_rewrites_history');
        $storeId = (int)$category->getStoreId();

        if ($category->getChangedProductIds()) {
            $this->generateChangedProductUrls($mergeDataProvider, $category, $storeId, $saveRewriteHistory);
        } else {
            $categoryStoreIds = $this->getCategoryStoreIds($category);

            foreach ($categoryStoreIds as $categoryStoreId) {
                $this->isSkippedProduct[$category->getEntityId()] = [];
                $mergeDataProvider->merge(
                    $this->getCategoryProductsUrlRewrites(
                        $category,
                        $categoryStoreId,
                        $saveRewriteHistory,
                        $category->getEntityId()
                    )
                );
            }
        }

        foreach ($this->childrenCategoriesProvider->getChildren($category, true) as $childCategory) {
            $mergeDataProvider->merge(
                $this->getCategoryProductsUrlRewrites(
                    $childCategory,
                    $storeId,
                    $saveRewriteHistory,
                    $category->getEntityId()
                )
            );
        }

        return $mergeDataProvider->getData();
    }

    /**
     * Update product url rewrites for changed product.
     *
     * @param Category $category
     * @return array
     */
    public function updateProductUrlRewritesForChangedProduct(Category $category): array
    {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $this->isSkippedProduct[$category->getEntityId()] = [];
        $saveRewriteHistory = (bool)$category->getData('save_rewrites_history');
        $storeIds = $this->getCategoryStoreIds($category);

        if ($category->getChangedProductIds()) {
            foreach ($storeIds as $storeId) {
                $this->generateChangedProductUrls($mergeDataProvider, $category, (int)$storeId, $saveRewriteHistory);
            }
        }

        return $mergeDataProvider->getData();
    }

    /**
     * Delete category rewrites for children.
     *
     * @param Category $category
     * @return void
     */
    public function deleteCategoryRewritesForChildren(Category $category)
    {
        $categoryIds = $this->childrenCategoriesProvider->getChildrenIds($category, true);
        $categoryIds[] = $category->getId();
        foreach ($categoryIds as $categoryId) {
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::ENTITY_ID => $categoryId,
                    UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::METADATA => $this->serializer->serialize(['category_id' => $categoryId]),
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
        }
    }

    /**
     * Get category products url rewrites.
     *
     * @param Category $category
     * @param int $storeId
     * @param bool $saveRewriteHistory
     * @param int|null $rootCategoryId
     * @return array
     */
    private function getCategoryProductsUrlRewrites(
        Category $category,
        $storeId,
        $saveRewriteHistory,
        $rootCategoryId = null
    ) {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $generateProductRewrite = (bool)$this->scopeConfig->getValue('catalog/seo/generate_category_product_rewrites');

        /** @var Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create();

        $productCollection->addCategoriesFilter(['eq' => [$category->getEntityId()]])
            ->addStoreFilter($storeId)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');

        foreach ($this->getProducts($productCollection) as $product) {
            if (isset($this->isSkippedProduct[$category->getEntityId()]) &&
                in_array($product->getId(), $this->isSkippedProduct[$category->getEntityId()])
            ) {
                continue;
            }
            $this->isSkippedProduct[$category->getEntityId()][] = $product->getId();
            $product->setStoreId($storeId);
            $product->setData('save_rewrites_history', $saveRewriteHistory);
            $product->setData('generate_rewrites', $generateProductRewrite);
            $mergeDataProvider->merge(
                $this->categoryBasedProductRewriteGenerator->generate($product, $rootCategoryId)
            );
        }

        return $mergeDataProvider->getData();
    }

    /**
     * Get products from provided collection
     *
     * @param Collection $collection
     * @return \Generator|Product[]
     */
    private function getProducts(Collection $collection): \Generator
    {
        $collection->setPageSize(1000);
        $pageCount = $collection->getLastPageNumber();
        $currentPage = 1;
        while ($currentPage <= $pageCount) {
            $collection->setCurPage($currentPage);
            foreach ($collection as $key => $product) {
                yield $key => $product;
            }
            $collection->clear();
            $currentPage++;
        }
    }

    /**
     * Generates product URL rewrites.
     *
     * @param MergeDataProvider $mergeDataProvider
     * @param Category $category
     * @param int $storeId
     * @param bool $saveRewriteHistory
     * @return void
     */
    private function generateChangedProductUrls(
        MergeDataProvider $mergeDataProvider,
        Category $category,
        int $storeId,
        bool $saveRewriteHistory
    ) {
        $this->isSkippedProduct[$category->getEntityId()] = $category->getAffectedProductIds();

        $categoryStoreIds = [$storeId];
        // If category is changed in the Global scope when need to regenerate product URL rewrites for all other scopes.
        if ($this->productScopeRewriteGenerator->isGlobalScope($storeId)) {
            $categoryStoreIds = $this->getCategoryStoreIds($category);
        }

        foreach ($categoryStoreIds as $categoryStoreId) {
            /* @var Collection $collection */
            $collection = $this->productCollectionFactory->create()
                ->setStoreId($categoryStoreId)
                ->addIdFilter($category->getChangedProductIds())
                ->addAttributeToSelect('visibility')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('url_path');

            $collection->setPageSize(1000);
            $pageCount = $collection->getLastPageNumber();
            $currentPage = 1;
            while ($currentPage <= $pageCount) {
                $collection->setCurPage($currentPage);
                foreach ($collection as $product) {
                    $product->setData('save_rewrites_history', $saveRewriteHistory);
                    $product->setStoreId($categoryStoreId);
                    $mergeDataProvider->merge(
                        $this->productUrlRewriteGenerator->generate($product, $category->getEntityId())
                    );
                }
                $collection->clear();
                $currentPage++;
            }
        }
    }

    /**
     * Gets category store IDs without Global Store.
     *
     * @param Category $category
     * @return array
     */
    private function getCategoryStoreIds(Category $category): array
    {
        $ids = $category->getStoreIds();
        return array_filter($ids);
    }
}
