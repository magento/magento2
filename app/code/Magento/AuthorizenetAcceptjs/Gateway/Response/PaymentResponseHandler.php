<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Response;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Processes payment information from a response
 */
class PaymentResponseHandler implements HandlerInterface
{
    private const RESPONSE_CODE_HELD = 4;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $transactionResponse = $response['transactionResponse'];

        if ($payment instanceof Payment) {
            $payment->setCcLast4($payment->getAdditionalInformation('ccLast4'));
            $payment->setCcAvsStatus($transactionResponse['avsResultCode']);
            $payment->setIsTransactionClosed(false);

            if ($transactionResponse['responseCode'] == self::RESPONSE_CODE_HELD) {
                $payment->setIsTransactionPending(true)
                    ->setIsFraudDetected(true);
            }
        }
    }
}
