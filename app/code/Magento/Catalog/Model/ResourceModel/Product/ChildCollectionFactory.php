<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product;

/**
 * Factory class for child product collection
 */
class ChildCollectionFactory extends CollectionFactory
{
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function create(array $data = [])
    {
        $collection = $this->_objectManager->create($this->_instanceName, $data);
        $collection->setFlag('product_children', true);
        return $collection;
    }
}
