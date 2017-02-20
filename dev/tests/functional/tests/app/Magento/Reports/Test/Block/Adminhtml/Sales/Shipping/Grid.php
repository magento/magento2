<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml\Sales\Shipping;

use Magento\Reports\Test\Block\Adminhtml\Sales\Orders\Viewed\FilterGrid;

/**
 * Shipping Report filter grid.
 */
class Grid extends FilterGrid
{
    /**
     * Filters row locator.
     *
     * @var string
     */
    protected $filterRows = '(//tr[td[contains(@class, "col-qty")]])[last()]/td[contains(@class, "col-%s")]';

    /**
     * Rows for get shipping result.
     *
     * @var array
     */
    protected $rows = [
        'qty',
        'total-sales-shipping',
        'total-shipping',
    ];
}
