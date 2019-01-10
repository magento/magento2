<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Response;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Processes payment information from a response
 */
class PaymentResponseHandler implements HandlerInterface
{

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param SubjectReader $subjectReader
     * @param Config $config
     */
    public function __construct(SubjectReader $subjectReader, Config $config)
    {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $action = $this->config->getPaymentAction($this->subjectReader->readStoreId($handlingSubject));
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $paymentDO->getPayment()->setAdditionalInformation('payment_type', $action);
        $payment = $paymentDO->getPayment();
        $transactionResponse = $response['transactionResponse'];

        // TODO use interface methods
        if (!$payment->getData(Payment::PARENT_TXN_ID)
            || $transactionResponse['transId'] != $payment->getParentTransactionId()
        ) {
            $payment->setTransactionId($transactionResponse['transId']);
        }
        $payment
            ->setTransactionAdditionalInfo(
                'real_transaction_id',
                $transactionResponse['transId']
            );
        $payment->setIsTransactionClosed(0);
    }
}
