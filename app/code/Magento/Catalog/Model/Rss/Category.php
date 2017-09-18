<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Rss;

/**
 * Class Category
 * @package Magento\Catalog\Model\Rss
 */
class Category
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $catalogLayer;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $visibility;

    /**
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product\Visibility $visibility
    ) {
        $this->catalogLayer = $layerResolver->get();
        $this->collectionFactory = $collectionFactory;
        $this->visibility = $visibility;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param int $storeId
     * @return $this
     */
    public function getProductCollection(\Magento\Catalog\Model\Category $category, $storeId)
    {
        /** @var $layer \Magento\Catalog\Model\Layer */
        $layer = $this->catalogLayer->setStore($storeId);
        $collection = $category->getResourceCollection();
        $collection->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_anchor')
            ->addAttributeToFilter('is_active', 1)
            ->addIdFilter($category->getChildren())
            ->load();
        /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $this->collectionFactory->create();

        $currentCategory = $layer->setCurrentCategory($category);
        $layer->prepareProductCollection($productCollection);
        $productCollection->addCountToCategories($collection);

        $category->getProductCollection()->setStoreId($storeId);

        $products = $currentCategory->getProductCollection()
            ->addAttributeToSort('updated_at', 'desc')
            ->setVisibility($this->visibility->getVisibleInCatalogIds())
            ->setCurPage(1)
            ->setPageSize(50);

        return $products;
    }
}
