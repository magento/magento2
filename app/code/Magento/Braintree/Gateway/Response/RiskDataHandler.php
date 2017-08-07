<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class RiskDataHandler
 * @since 2.1.0
 */
class RiskDataHandler implements HandlerInterface
{
    /**
     * Risk data id
     */
    const RISK_DATA_ID = 'riskDataId';

    /**
     * The possible values of the risk decision are Not Evaluated, Approve, Review, and Decline
     */
    const RISK_DATA_DECISION = 'riskDataDecision';

    /**
     * Risk data Review status
     * @since 2.1.3
     */
    private static $statusReview = 'Review';

    /**
     * @var SubjectReader
     * @since 2.1.0
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     * @since 2.1.0
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @since 2.1.0
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        /** @var \Braintree\Transaction $transaction */
        $transaction = $this->subjectReader->readTransaction($response);

        if (!isset($transaction->riskData)) {
            return;
        }

        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $payment->setAdditionalInformation(self::RISK_DATA_ID, $transaction->riskData->id);
        $payment->setAdditionalInformation(self::RISK_DATA_DECISION, $transaction->riskData->decision);

        // mark payment as fraud
        if ($transaction->riskData->decision === self::$statusReview) {
            $payment->setIsFraudDetected(true);
        }
    }
}
