<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml\Sales\Orders\Viewed;

use Magento\Backend\Test\Block\Widget\Grid;
use Magento\Mtf\Client\Locator;

/**
 * Class FilterGrid
 * Sales Report filter grid
 */
class FilterGrid extends Grid
{
    /**
     * Filters row locator
     *
     * @var string
     */
    protected $filterRows = '(//tr[td[contains(@class, "col-orders")]])[last()]/td[contains(@class, "col-%s")]';

    /**
     * Filters row locator
     *
     * @var string
     */
    protected $totalRows = '//tfoot/tr/th[contains(@class, "col-%s")]';

    /**
     * Rows for get sales result
     *
     * @var array
     */
    protected $rows = [
        'orders',
        'sales-items',
        'sales-total',
        'invoiced',
    ];

    /**
     * Get last row data from report grid
     *
     * @return array
     */
    public function getLastResult()
    {
        return $this->getResults($this->filterRows);
    }

    /**
     * Get total data from report grid
     *
     * @return array
     */
    public function getTotalResult()
    {
        return $this->getResults($this->totalRows);
    }

    /**
     * Get data from report grid
     *
     * @param string $filterRows
     * @return array
     */
    protected function getResults($filterRows)
    {
        $orders = [];
        $row = $this->_rootElement->find(sprintf($filterRows, $this->rows[0]), Locator::SELECTOR_XPATH);
        if (!$row->isVisible()) {
            return array_fill_keys($this->rows, 0);
        }
        foreach ($this->rows as $row) {
            $value = $this->_rootElement->find(sprintf($filterRows, $row), Locator::SELECTOR_XPATH)->getText();
            $orders[$row] = preg_replace('`[$,]`', '', $value);
        }

        return $orders;
    }
}
