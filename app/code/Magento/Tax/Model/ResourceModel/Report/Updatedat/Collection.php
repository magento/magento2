<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * Tax report collection
 */
namespace Magento\Tax\Model\ResourceModel\Report\Updatedat;

class Collection extends \Magento\Tax\Model\ResourceModel\Report\Collection
{
    /**
     * Aggregated Data Table
     *
     * @var string
     */
    protected $_aggregationTable = 'tax_order_aggregated_updated';
}
