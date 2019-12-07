<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Transactions;

use Magento\Mtf\Client\Locator;

/**
 * Transactions grid on order view page.
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Locator for transaction id.
     *
     * @var string
     */
    protected $txnId = './/tbody//td[@data-column="txn_id"]';

    /**
     * Locator for transaction status.
     *
     * @var string
     */
    protected $txnStatus = './..//td[@data-column="is_closed"]';

    /**
     * Locator for transaction type.
     *
     * @var string
     */
    protected $txnType = './..//td[@data-column="txn_type"]';

    /**
     * Get transaction ids
     *
     * @return array
     */
    public function getIds()
    {
        $result = [];
        $txnIds = $this->_rootElement->getElements($this->txnId, Locator::SELECTOR_XPATH);
        foreach ($txnIds as $txnId) {
            $result[trim($txnId->getText())] = [
                'transactionType' => $txnId->find($this->txnType, Locator::SELECTOR_XPATH)->getText(),
                'statusIsClosed' => $txnId->find($this->txnStatus, Locator::SELECTOR_XPATH)->getText()
            ];
        }
        return $result;
    }
}
