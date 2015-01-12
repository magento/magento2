<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml\Sales\Refunded;

/**
 * Class FilterGrid
 * Sales Refunded Report filter grid
 */
class FilterGrid extends \Magento\Reports\Test\Block\Adminhtml\Sales\Orders\Viewed\FilterGrid
{
    /**
     * Filters row locator
     *
     * @var string
     */
    protected $filterRows = '(//tr[td[contains(@class, "col-orders_count")]])[last()]/td[contains(@class, "col-%s")]';

    /**
     * Rows for get sales result
     *
     * @var array
     */
    protected $rows = [
        'orders_count',
        'refunded',
    ];
}
