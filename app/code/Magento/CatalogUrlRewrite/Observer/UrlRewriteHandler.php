<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

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

    /** @var \Magento\Framework\Serialize\Serializer\Json */
    private $serializer;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider $childrenCategoriesProvider,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productCollectionFactory = $productCollectionFactory;
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
     * @param \Magento\Catalog\Model\Category $category
     * @param int $storeId
     * @param bool $saveRewriteHistory
     * @return UrlRewrite[]
     */
    public function getCategoryProductsUrlRewrites(
        \Magento\Catalog\Model\Category $category,
        $storeId,
        $saveRewriteHistory
    )
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
            $productUrls = array_merge(
                $productUrls,
                $this->getCategoryBasedProductRewriteGenerator()->generate($product, $category)
            );
        }
        return $productUrls;
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
                    UrlRewrite::ENTITY_ID => $categoryId,
                    UrlRewrite::ENTITY_TYPE =>
                        \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::METADATA => $this->serializer->serialize(['category_id' => $categoryId]),
                    UrlRewrite::ENTITY_TYPE =>
                        \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
        }
    }
}
