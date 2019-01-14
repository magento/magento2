<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Response;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Gateway\Validator\TransactionResponseValidator;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Processes payment information from a response
 */
class PaymentResponseHandler implements HandlerInterface
{
    const REAL_TRANSACTION_ID = 'real_transaction_id';

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
        $storeId = $this->subjectReader->readStoreId($handlingSubject);
        $action = $this->config->getPaymentAction($storeId);
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $paymentDO->getPayment()->setAdditionalInformation('payment_type', $action);
        $payment = $paymentDO->getPayment();
        $transactionResponse = $response['transactionResponse'];

        if ($payment instanceof Payment) {
            if (!$payment->getParentTransactionId()
                || $transactionResponse['transId'] != $payment->getParentTransactionId()
            ) {
                $payment->setTransactionId($transactionResponse['transId']);
            }
            $payment
                ->setTransactionAdditionalInfo(
                    self::REAL_TRANSACTION_ID,
                    $transactionResponse['transId']
                );
            $payment->setCcAvsStatus($transactionResponse['avsResultCode']);
            $payment->setIsTransactionClosed(false);

            if ($transactionResponse['responseCode'] == TransactionResponseValidator::RESPONSE_CODE_HELD) {
                $payment->setIsTransactionPending(true)
                    ->setIsFraudDetected(true);
            }

            // TODO set any available cc info like last 4
        }

        $additionalInformationKeys = $this->config->getAdditionalInfoKeys($storeId);
        $rawDetails = [];
        foreach ($additionalInformationKeys as $paymentInfoKey) {
            if (isset($transactionResponse[$paymentInfoKey])) {
                $rawDetails[$paymentInfoKey] = $transactionResponse[$paymentInfoKey];
            }
        }
        $payment->setTransactionAdditionalInfo(Payment\Transaction::RAW_DETAILS, $rawDetails);
    }
}
