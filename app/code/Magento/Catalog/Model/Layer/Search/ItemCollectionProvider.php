<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Search;

use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class \Magento\Catalog\Model\Layer\Search\ItemCollectionProvider
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
        return $this->collectionFactory->create();
    }
}
