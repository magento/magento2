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
 * Catalog search category layer collection provider.
 */
class ItemCollectionProvider implements ItemCollectionProviderInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getCollection(\Magento\Catalog\Model\Category $category)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addCategoryFilter($category);
        return $collection;
    }
}
