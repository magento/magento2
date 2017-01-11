<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create;

use Magento\Mtf\Block\Block;

/**
 * Class Totals
 * Adminhtml sales order create totals block
 *
 */
class Totals extends Block
{
    /**
     * 'Submit Order' button
     *
     * @var string
     */
    protected $submitOrder = '.order-totals-actions button';

    /**
     * Order totals table
     *
     * @var string
     */
    protected $totalsTable = '.data-table';

    /**
     * Click 'Submit Order' button
     */
    public function submitOrder()
    {
        $this->_rootElement->find($this->submitOrder)->click();
    }

    /**
     * Return totals by labels
     *
     * @param $totals string[]
     * @return array
     */
    public function getTotals($totals)
    {
        if (empty ($totals)) {
            return [];
        }

        $totalsResult = [];

        $totalsTable = $this->_rootElement->find($this->totalsTable);
        $rawTable = $totalsTable->getText();
        $existingTotals = explode(PHP_EOL, $rawTable);
        foreach ($totals as $total) {
            foreach ($existingTotals as $rowTotal) {
                if (strpos($rowTotal, $total) !== false) {
                    $totalValue = trim(str_replace($total, '', $rowTotal));
                    $totalsResult[$total] = $this->_escapeNumericValue($totalValue);
                }
            }
        }

        return $totalsResult;
    }

    /**
     * Escape numeric value
     *
     * @param string $value
     * @return mixed
     */
    private function _escapeNumericValue($value)
    {
        return preg_replace("/[^-0-9\\.]/", "", $value);
    }
}
