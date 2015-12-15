<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Response;

use Braintree\Transaction;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CaptureDetailsHandler
 */
class CaptureDetailsHandler implements HandlerInterface
{

    /**
     * @var \Magento\Sales\Model\Order\Payment
     */
    protected $payment;

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        /**
         * @TODO after changes in sales module should be refactored for new interfaces
         */
        $this->payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($this->payment);

        $this->payment->setIsTransactionClosed(false);
    }
}