<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class UrlRewriteHandler
{
    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider */
    protected $childrenCategoriesProvider;

    /** @var CategoryUrlRewriteGenerator */
    protected $categoryUrlRewriteGenerator;

    /** @var ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /** @var UrlPersistInterface */
    protected $urlPersist;

    /** @var array */
    protected $isSkippedProduct;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    protected $productCollectionFactory;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Generate url rewrites for products assigned to category
     *
     * @param Category $category
     * @return array
     */
    public function generateProductUrlRewrites(Category $category)
    {
        $this->isSkippedProduct = [];
        $saveRewriteHistory = $category->getData('save_rewrites_history');
        $storeId = $category->getStoreId();
        $productUrls = [];
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
                $productUrls = array_merge($productUrls, $this->productUrlRewriteGenerator->generate($product));
            }
        } else {
            $productUrls = array_merge(
                $productUrls,
                $this->getCategoryProductsUrlRewrites($category, $storeId, $saveRewriteHistory)
            );
        }
        foreach ($this->childrenCategoriesProvider->getChildren($category, true) as $childCategory) {
            $productUrls = array_merge(
                $productUrls,
                $this->getCategoryProductsUrlRewrites($childCategory, $storeId, $saveRewriteHistory)
            );
        }
        return $productUrls;
    }

    /**
     * @param Category $category
     * @param int $storeId
     * @param bool $saveRewriteHistory
     * @return UrlRewrite[]
     */
    public function getCategoryProductsUrlRewrites(Category $category, $storeId, $saveRewriteHistory)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $category->getProductCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');
        $productUrls = [];
        foreach ($productCollection as $product) {
            if (in_array($product->getId(), $this->isSkippedProduct)) {
                continue;
            }
            $this->isSkippedProduct[] = $product->getId();
            $product->setStoreId($storeId);
            $product->setData('save_rewrites_history', $saveRewriteHistory);
            $productUrls = array_merge($productUrls, $this->productUrlRewriteGenerator->generate($product));
        }
        return $productUrls;
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
                    UrlRewrite::ENTITY_ID => $categoryId,
                    UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::METADATA => serialize(['category_id' => $categoryId]),
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
        }
    }
}
