<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Reports\Model\ResourceModel\Report\Product\Viewed;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection
{
    /**
     * Tables per period
     *
     * @var array
     */
    protected $tableForPeriod = [
        'daily' => \Magento\Reports\Model\ResourceModel\Report\Product\Viewed::AGGREGATION_DAILY,
        'monthly' => \Magento\Reports\Model\ResourceModel\Report\Product\Viewed::AGGREGATION_MONTHLY,
        'yearly' => \Magento\Reports\Model\ResourceModel\Report\Product\Viewed::AGGREGATION_YEARLY,
    ];

    /**
     * Return ordered filed
     *
     * @return string
     */
    protected function getOrderedField()
    {
        return 'views_num';
    }
}
