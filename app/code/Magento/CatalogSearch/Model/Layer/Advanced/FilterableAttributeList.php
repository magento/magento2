<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Layer\Advanced;

class FilterableAttributeList extends \Magento\Catalog\Model\Layer\Category\FilterableAttributeList
{
    /**
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver
    ) {
        parent::__construct($collectionFactory, $storeManager, $layerResolver);
    }

    /**
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\Collection $collection
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    protected function _prepareAttributeCollection($collection)
    {
        $collection->addDisplayInAdvancedSearchFilter()->addVisibleFilter();
        return $collection;
    }
}
