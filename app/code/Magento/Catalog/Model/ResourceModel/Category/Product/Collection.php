<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Category\Product;

use Magento\Catalog\Api\Data\CategoryProductSearchResultInterface;
use Magento\Catalog\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection implements CategoryProductSearchResultInterface
{
    /**
     * Id field name
     *
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\CategoryProduct', 'Magento\Catalog\Model\ResourceModel\CategoryProduct');
    }

    /**
     * Used to emulate after load functionality for each item without loading them
     *
     * @return void
     */
    protected function _afterLoad()
    {
        $this->walk('afterLoad');
    }
}
