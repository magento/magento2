<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

class UrlRewriteHandler
{
    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider */
    protected $childrenCategoriesProvider;

    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator */
    protected $categoryUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /** @var \Magento\UrlRewrite\Model\UrlPersistInterface */
    protected $urlPersist;

    /** @var array */
    protected $isSkippedProduct;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    protected $productCollectionFactory;

    /** @var \Magento\CatalogUrlRewrite\Model\CategoryBasedProductRewriteGenerator */
    private $categoryBasedProductRewriteGenerator;

    /** @var \Magento\UrlRewrite\Model\MergeDataProvider */
    private $mergeDataProviderPrototype;

    /** @var \Magento\Framework\Serialize\Serializer\Json */
    private $serializer;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\UrlRewrite\Model\MergeDataProviderFactory|null $mergeDataProviderFactory
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\UrlRewrite\Model\MergeDataProviderFactory $mergeDataProviderFactory = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productCollectionFactory = $productCollectionFactory;

        if (!isset($mergeDataProviderFactory)) {
            $mergeDataProviderFactory =  \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\UrlRewrite\Model\MergeDataProviderFactory::class
            );
        }

        $this->mergeDataProviderPrototype = $mergeDataProviderFactory->create();

        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Serialize\Serializer\Json::class
        );
    }

    /**
     * Generate url rewrites for products assigned to category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return array
     */
    public function generateProductUrlRewrites(\Magento\Catalog\Model\Category $category)
    {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        $this->isSkippedProduct = [];
        $saveRewriteHistory = $category->getData('save_rewrites_history');
        $storeId = $category->getStoreId();
        if ($category->getAffectedProductIds()) {
            $this->isSkippedProduct = $category->getAffectedProductIds();
            $collection = $this->productCollectionFactory->create()
                ->setStoreId($storeId)
                ->addIdFilter($category->getAffectedProductIds())
                ->addAttributeToSelect('visibility')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('url_path');
            foreach ($collection as $product) {
                $product->setStoreId($storeId);
                $product->setData('save_rewrites_history', $saveRewriteHistory);
                $mergeDataProvider->merge(
                    $this->productUrlRewriteGenerator->generate($product, $category->getEntityId())
                );
            }
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
     * @param \Magento\Catalog\Model\Category $category
     * @param int $storeId
     * @param bool $saveRewriteHistory
     * @param int|null $rootCategoryId
     * @return array
     */
    public function getCategoryProductsUrlRewrites(
        \Magento\Catalog\Model\Category $category,
        $storeId,
        $saveRewriteHistory,
        $rootCategoryId = null
    ) {
        $mergeDataProvider = clone $this->mergeDataProviderPrototype;
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $category->getProductCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');
        foreach ($productCollection as $product) {
            if (in_array($product->getId(), $this->isSkippedProduct)) {
                continue;
            }
            $this->isSkippedProduct[] = $product->getId();
            $product->setStoreId($storeId);
            $product->setData('save_rewrites_history', $saveRewriteHistory);
            $mergeDataProvider->merge(
                $this->getCategoryBasedProductRewriteGenerator()->generate($product, $category, $rootCategoryId)
            );
        }

        return $mergeDataProvider->getData();
    }

    /**
     * Retrieve generator, which use single category for different products
     *
     * @deprecated
     * @return \Magento\CatalogUrlRewrite\Model\CategoryBasedProductRewriteGenerator|mixed
     */
    private function getCategoryBasedProductRewriteGenerator()
    {
        if (!$this->categoryBasedProductRewriteGenerator) {
            $this->categoryBasedProductRewriteGenerator = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\CatalogUrlRewrite\Model\CategoryBasedProductRewriteGenerator::class);
        }

        return $this->categoryBasedProductRewriteGenerator;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return void
     */
    public function deleteCategoryRewritesForChildren(\Magento\Catalog\Model\Category $category)
    {
        $categoryIds = $this->childrenCategoriesProvider->getChildrenIds($category, true);
        $categoryIds[] = $category->getId();
        foreach ($categoryIds as $categoryId) {
            $this->urlPersist->deleteByData(
                [
                    \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_ID =>
                        $categoryId,
                    \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE =>
                        \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
            $this->urlPersist->deleteByData(
                [
                    \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::METADATA =>
                        $this->serializer->serialize(['category_id' => $categoryId]),
                    \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE =>
                        \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
        }
    }
}
