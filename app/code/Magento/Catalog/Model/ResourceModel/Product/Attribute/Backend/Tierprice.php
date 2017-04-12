<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice;

/**
 * Catalog product tier price backend attribute model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tierprice extends AbstractGroupPrice
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity_tier_price', 'value_id');
    }

    /**
     * Add qty column
     *
     * @param array $columns
     * @return array
     */
    protected function _loadPriceDataColumns($columns)
    {
        $columns = parent::_loadPriceDataColumns($columns);
        $columns['price_qty'] = 'qty';
        $columns['percentage_value'] = 'percentage_value';
        return $columns;
    }

    /**
     * Order by qty
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    protected function _loadPriceDataSelect($select)
    {
        $select->order('qty');
        return $select;
    }
}
