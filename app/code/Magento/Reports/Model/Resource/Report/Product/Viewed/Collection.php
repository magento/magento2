<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Model\Resource\Report\Product\Viewed;

class Collection extends \Magento\Sales\Model\Resource\Report\Bestsellers\Collection
{
    /**
     * Tables per period
     *
     * @var array
     */
    protected $tableForPeriod = [
        'daily' => \Magento\Reports\Model\Resource\Report\Product\Viewed::AGGREGATION_DAILY,
        'monthly' => \Magento\Reports\Model\Resource\Report\Product\Viewed::AGGREGATION_MONTHLY,
        'yearly' => \Magento\Reports\Model\Resource\Report\Product\Viewed::AGGREGATION_YEARLY,
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
