<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;
use Magento\CatalogUrlRewrite\Model\CategoryProductUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\UrlRewrite\Model\MergeDataProvider;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
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
     * @param ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param CollectionFactory $productCollectionFactory
     * @param CategoryProductUrlPathGenerator $categoryBasedProductRewriteGenerator
     * @param MergeDataProviderFactory|null $mergeDataProviderFactory
     * @param Json|null $serializer
     * @param ProductScopeRewriteGenerator|null $productScopeRewriteGenerator
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
        ProductScopeRewriteGenerator $productScopeRewriteGenerator = null
    ) {
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryBasedProductRewriteGenerator = $categoryBasedProductRewriteGenerator;

        $objectManager = ObjectManager::getInstance();
        $mergeDataProviderFactory = $mergeDataProviderFactory ?: $objectManager->get(MergeDataProviderFactory::class);
        $this->mergeDataProviderPrototype = $mergeDataProviderFactory->create();
        $this->serializer = $serializer ?: $objectManager->get(Json::class);
        $this->productScopeRewriteGenerator = $productScopeRewriteGenerator
            ?: $objectManager->get(ProductScopeRewriteGenerator::class);
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
        $saveRewriteHistory = $category->getData('save_rewrites_history');
        $storeId = (int)$category->getStoreId();

        if ($category->getChangedProductIds()) {
            $this->generateChangedProductUrls($mergeDataProvider, $category, $storeId, $saveRewriteHistory);
        } else {
            $mergeDataProvider->merge(
                $this->getCategoryProductsUrlRewrites(
                    $category,
                    $storeId,
                    $saveRewriteHistory,
                    $category->getEntityId()
                )
            );
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
                    UrlRewrite::ENTITY_ID =>
                        $categoryId,
                    UrlRewrite::ENTITY_TYPE =>
                        CategoryUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::METADATA =>
                        $this->serializer->serialize(['category_id' => $categoryId]),
                    UrlRewrite::ENTITY_TYPE =>
                        ProductUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
        }
    }

    /**
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

        /** @var Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create();

        $productCollection->addCategoriesFilter(['eq' => [$category->getEntityId()]])
            ->setStoreId($storeId)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');

        foreach ($productCollection as $product) {
            if (isset($this->isSkippedProduct[$category->getEntityId()]) &&
                in_array($product->getId(), $this->isSkippedProduct[$category->getEntityId()])
            ) {
                continue;
            }
            $this->isSkippedProduct[$category->getEntityId()][] = $product->getId();
            $product->setStoreId($storeId);
            $product->setData('save_rewrites_history', $saveRewriteHistory);
            $mergeDataProvider->merge(
                $this->categoryBasedProductRewriteGenerator->generate($product, $rootCategoryId)
            );
        }

        return $mergeDataProvider->getData();
    }

    /**
     * Generates product URL rewrites.
     *
     * @param MergeDataProvider $mergeDataProvider
     * @param Category $category
     * @param Product $product
     * @param int $storeId
     * @param $saveRewriteHistory
     */
    private function generateChangedProductUrls(
        MergeDataProvider $mergeDataProvider,
        Category $category,
        int $storeId,
        $saveRewriteHistory
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
                ->addIdFilter($category->getAffectedProductIds())
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
