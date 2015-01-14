<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Transaction\Grid;

/**
 * Sales transaction types option array
 */
class TypeList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Sales\Model\Order\Payment\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @param \Magento\Sales\Model\Order\Payment\TransactionFactory $transactionFactory
     */
    public function __construct(\Magento\Sales\Model\Order\Payment\TransactionFactory $transactionFactory)
    {
        $this->_transactionFactory = $transactionFactory;
    }

    /**
     * Return option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_transactionFactory->create()->getTransactionTypes();
    }
}
