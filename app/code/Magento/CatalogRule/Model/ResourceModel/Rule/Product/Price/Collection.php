<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Magento\CatalogRule\Model\Rule\Product\Price::class,
            \Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price::class
        );
    }

    /**
     * @return array
     * @api
     */
    public function getProductIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns('main_table.product_id');
        $idsSelect->distinct(true);
        return $this->getConnection()->fetchCol($idsSelect);
    }
}
