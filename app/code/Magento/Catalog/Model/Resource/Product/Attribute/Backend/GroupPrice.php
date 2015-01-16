<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Resource\Product\Attribute\Backend;

use Magento\Catalog\Model\Resource\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice;

/**
 * Catalog product group price backend attribute model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class GroupPrice extends AbstractGroupPrice
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity_group_price', 'value_id');
    }
}
