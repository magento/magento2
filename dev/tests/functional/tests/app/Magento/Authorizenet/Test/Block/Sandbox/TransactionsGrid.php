<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Test\Block\Sandbox;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Transactions grid block.
 */
class TransactionsGrid extends Block
{
    /**
     * Transaction selector.
     *
     * @var string
     */
    private $transaction = './/a[contains(text(), "%s")]';

    /**
     * 'Approve' button selector.
     *
     * @var string
     */
    private $transactionApprove = '(//input[@id="btnConfirmApprove"])[1]';

    /**
     * Confirmation window 'OK' button selector.
     *
     * @var string
     */
    private $transactionApprovalConfirm = '#btnConfirmYes';

    /**
     * Find transaction in grid and open it.
     *
     * @param $transactionId
     * @return void
     */
    public function openTransaction($transactionId)
    {
        $this->_rootElement->find(sprintf($this->transaction, $transactionId), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Approve selected transaction.
     *
     * @return void
     */
    public function approveTransaction()
    {
        $this->_rootElement->find($this->transactionApprove, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Confirm approval of selected transaction.
     *
     * @return void
     */
    public function confirmTransactionApproval()
    {
        $this->browser->find($this->transactionApprovalConfirm)->click();
    }
}
