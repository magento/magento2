<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Response;

use Braintree\Transaction;
use Magento\BraintreeTwo\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class CloneDetailsHandler
 */
class CloneDetailsHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        /**
         * @TODO after changes in sales module should be refactored for new interfaces
         */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $transaction = $this->subjectReader->readTransaction($response);

        $payment->setTransactionId($transaction->id);
    }
}
