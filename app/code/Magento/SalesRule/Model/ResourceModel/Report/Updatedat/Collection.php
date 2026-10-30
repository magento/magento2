<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Model\ResourceModel\Report\Updatedat;

/**
 * Sales report coupons collection
 */
class Collection extends \Magento\SalesRule\Model\ResourceModel\Report\Collection
{
    /**
     * Aggregated Data Table
     *
     * @var string
     */
    protected $_aggregationTable = 'salesrule_coupon_aggregated_updated';
}
