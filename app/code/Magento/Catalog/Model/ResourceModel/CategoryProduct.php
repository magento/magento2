<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class CategoryProduct
 * @since 2.1.0
 */
class CategoryProduct extends AbstractDb
{
    /**
     * Event prefix
     *
     * @var string
     * @since 2.1.0
     */
    protected $_eventPrefix = 'catalog_category_product_resource';

    /**
     * Model initialization
     *
     * @return void
     * @since 2.1.0
     */
    protected function _construct()
    {
        $this->_init('catalog_category_product', 'entity_id');
    }
}
