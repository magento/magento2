<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

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
     * Click 'Submit Order' button
     */
    public function submitOrder()
    {
        $this->_rootElement->find($this->submitOrder)->click();
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
}
