<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Reports Compared Product Index Resource Collection
 */
namespace Magento\Reports\Model\ResourceModel\Product\Index\Compared;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Product\Index\Collection\AbstractCollection
{
    /**
     * Retrieve Product Index table name
     *
     * @return string
     */
    protected function _getTableName()
    {
        return $this->getTable('report_compared_product_index');
    }
}
