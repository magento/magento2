<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\ProductList;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Class CollectionLoader
 *
 * @api
 * @since 100.0.2
 */
class CollectionLoader
{
    /**
     * @param AbstractCollection $collection
     *
     * @return AbstractCollection
     */
    public function load($collection) {
        $collection->load();
        return $collection;
    }
}
