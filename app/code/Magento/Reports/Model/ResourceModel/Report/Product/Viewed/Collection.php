<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Model\ResourceModel\Report\Product\Viewed;

/**
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection
{
    /**
     * Tables per period
     *
     * @var array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function getOrderedField()
    {
        return 'views_num';
    }
}
