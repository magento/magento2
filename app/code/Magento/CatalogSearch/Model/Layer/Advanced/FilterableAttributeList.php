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
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\Collection $collection
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    protected function _prepareAttributeCollection($collection)
    {
        $collection->addDisplayInAdvancedSearchFilter()->addVisibleFilter();
        return $collection;
    }
}
