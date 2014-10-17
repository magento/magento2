<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Reports\Test\Block\Adminhtml\Sales\Orders\Viewed;

use Magento\Backend\Test\Block\Widget\Grid;
use Mtf\Client\Element\Locator;

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
