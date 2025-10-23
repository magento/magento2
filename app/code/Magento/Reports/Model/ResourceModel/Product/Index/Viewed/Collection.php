<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Reports Viewed Product Index Resource Collection
 */
namespace Magento\Reports\Model\ResourceModel\Product\Index\Viewed;

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
        return $this->getTable('report_viewed_product_index');
    }
}
