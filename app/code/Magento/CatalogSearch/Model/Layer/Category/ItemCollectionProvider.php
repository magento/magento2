<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Layer\Category;

use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider
 *
 * @since 2.0.0
 */
class ItemCollectionProvider implements ItemCollectionProviderInterface
{
    /**
     * @var CollectionFactory
     * @since 2.0.0
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @since 2.0.0
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @since 2.0.0
     */
    public function getCollection(\Magento\Catalog\Model\Category $category)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addCategoryFilter($category);
        return $collection;
    }
}
