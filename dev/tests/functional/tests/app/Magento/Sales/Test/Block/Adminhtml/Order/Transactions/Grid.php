<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Transactions;

use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

/**
 * Class Grid
 * Sales order grid
 *
 */
class Grid extends GridInterface
{
    /**
     * {@inheritdoc}
     */
    protected $filters = [
        'id' => [
            'selector' => '#order_transactions_filter_txn_id',
        ],
    ];

    /**
     * Transaction type
     *
     * @var string
     */
    protected $transactionType = 'td.col-transaction-type.col-txn_type';

    /**
     * Get Transaction type
     *
     * @return array|string
     */
    public function getTransactionType()
    {
        return $this->_rootElement->find($this->transactionType)->getText();
    }
}
