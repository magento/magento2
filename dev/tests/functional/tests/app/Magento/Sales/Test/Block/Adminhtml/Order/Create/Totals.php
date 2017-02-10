<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Adminhtml sales order create totals block.
 */
class Totals extends Block
{
    /**
     * 'Submit Order' button.
     *
     * @var string
     */
    protected $submitOrder = '.order-totals-actions button';

    /**
     * Order totals table.
     *
     * @var string
     */
    protected $totalsTable = '.data-table';

    /**
     * Total row label selector.
     *
     * @var string
     */
    protected $totalLabelLocator = './/tr[normalize-space(td)="%s"]';

    /**
     * Total value selector.
     *
     * @var string
     */
    protected $totalValueLocator = './/td/span[contains(@class,"price")]';

    /**
     * Click 'Submit Order' button.
     */
    public function submitOrder()
    {
        $this->_rootElement->find($this->submitOrder)->click();
    }

    /**
     * Return totals by labels.
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

        foreach ($totals as $total) {

            $totalRow = $totalsTable->find(sprintf($this->totalLabelLocator, $total), Locator::SELECTOR_XPATH);
            if ($totalRow->isVisible()) {
                $totalValue = $totalRow->find($this->totalValueLocator, Locator::SELECTOR_XPATH);
                $totalsResult[$total] = $this->escapeNumericValue($totalValue->getText());
            }
        }

        return $totalsResult;
    }

    /**
     * Return total presence by label.
     *
     * @param string $total
     * @return bool
     */
    public function isTotalPresent($total)
    {
        $totalsTable = $this->_rootElement->find($this->totalsTable);
        $totalRow = $totalsTable->find(sprintf($this->totalLabelLocator, $total), Locator::SELECTOR_XPATH);
        return $totalRow->isVisible();
    }

    /**
     * Escape numeric value.
     *
     * @param string $value
     * @return mixed
     */
    private function escapeNumericValue($value)
    {
        return preg_replace("/[^-0-9\\.]/", "", $value);
    }
}
