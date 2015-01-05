<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogSearch\Model\Layer\Category;

use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\Resource\Product\CollectionFactory;

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
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    public function getCollection(\Magento\Catalog\Model\Category $category)
    {
        /** @var \Magento\Catalog\Model\Resource\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addCategoryFilter($category);
        return $collection;
    }
}
