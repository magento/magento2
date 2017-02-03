<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Plugin\Sales\Order;

use Magento\Braintree\Model\PaymentMethod;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory as TransactionCollectionFactory;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;

class PaymentPlugin
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Braintree\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var TransactionCollectionFactory
     */
    protected $salesTransactionCollectionFactory;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param TransactionCollectionFactory $salesTransactionCollectionFactory
     * @param \Magento\Braintree\Helper\Data $paymentHelper
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        TransactionCollectionFactory $salesTransactionCollectionFactory,
        \Magento\Braintree\Helper\Data $paymentHelper
    ) {
        $this->registry = $registry;
        $this->salesTransactionCollectionFactory = $salesTransactionCollectionFactory;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Magento will consider a transaction for voiding only if it is an authorization
     * Braintree allows voiding captures too
     *
     * Lookup an authorization transaction using parent transaction id, if set
     *
     * @param Payment $subject
     * @param callable $proceed
     *
     * @return \Magento\Sales\Model\Order\Payment\Transaction|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundGetAuthorizationTransaction(
        Payment $subject,
        \Closure $proceed
    ) {
        if ($subject->getMethodInstance()->getCode() != PaymentMethod::METHOD_CODE) {
            return $proceed();
        }
        $invoice = $this->registry->registry('current_invoice');
        if ($invoice && $invoice->getId()) {
            $transactionId = $this->paymentHelper
                ->clearTransactionId($invoice->getTransactionId());
            $collection = $this->salesTransactionCollectionFactory->create()
                ->addFieldToFilter('txn_id', ['eq' => $transactionId]);
            if ($collection->getSize() < 1) {
                return $proceed();
            } else {
                return $collection->getFirstItem();
            }
        }
        return $proceed();
    }
}
