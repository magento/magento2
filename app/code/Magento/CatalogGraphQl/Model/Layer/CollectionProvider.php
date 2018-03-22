<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogGraphQl\Model\Layer;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Class CollectionProvider
 * @package Magento\CatalogGraphQl\Model\Layer
 */
class CollectionProvider implements \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Collection
     */
    private $collection;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getCollection(\Magento\Catalog\Model\Category $category)
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create();
        }
        return $this->collection;
    }
}