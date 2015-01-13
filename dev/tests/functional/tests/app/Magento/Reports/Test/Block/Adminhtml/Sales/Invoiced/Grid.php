<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml\Sales\Invoiced;

use Magento\Reports\Test\Block\Adminhtml\Sales\Orders\Viewed\FilterGrid;

/**
 * Class Grid
 * Invoice Report filter grid
 */
class Grid extends FilterGrid
{
    /**
     * Filters row locator
     *
     * @var string
     */
    protected $filterRows = '(//tr[td[contains(@class, "col-qty")]])[last()]/td[contains(@class, "col-%s")]';

    /**
     * Rows for get invoice result
     *
     * @var array
     */
    protected $rows = [
        'qty',
        'invoiced',
        'total-invoiced',
    ];
}
