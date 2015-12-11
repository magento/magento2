<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Response;

use Braintree\Transaction;
use Magento\BraintreeTwo\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Payment Details Handler
 * @package Magento\BraintreeTwo\Gateway\Response
 */
class PaymentDetailsHandler implements HandlerInterface
{
    const AVS_POSTAL_RESPONSE_CODE = 'avsPostalCodeResponseCode';

    const AVS_STREET_ADDRESS_RESPONSE_CODE = 'avsStreetAddressResponseCode';

    const CVV_RESPONSE_CODE = 'cvvResponseCode';

    const PROCESSOR_AUTHORIZATION_CODE = 'processorAuthorizationCode';

    const PROCESSOR_RESPONSE_CODE = 'processorResponseCode';

    const PROCESSOR_RESPONSE_TEXT = 'processorResponseText';

    const LIABILITY_SHIFTED = 'liabilityShifted';

    const LIABILITY_SHIFT_POSSIBLE = 'liabilityShiftPossible';

    /**
     * List of additional details
     * @var array
     */
    protected $additionalInformationMapping = [
        self::AVS_POSTAL_RESPONSE_CODE,
        self::AVS_STREET_ADDRESS_RESPONSE_CODE,
        self::CVV_RESPONSE_CODE,
        self::PROCESSOR_AUTHORIZATION_CODE,
        self::PROCESSOR_RESPONSE_CODE,
        self::PROCESSOR_RESPONSE_TEXT,
    ];

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        /** @var \Braintree\Transaction $transaction */
        $transaction = $response['object']->transaction;
        /**
         * @TODO after changes in sales module should be refactored for new interfaces
         */
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $payment->setTransactionId($transaction->id);
        $payment->setCcTransId($transaction->id);
        $payment->setLastTransId($transaction->id);
        $payment->setIsTransactionClosed(false);

        $this->process3DSecure($transaction, $payment);
        //remove previously set payment nonce
        $payment->unsAdditionalInformation(DataAssignObserver::PAYMENT_METHOD_NONCE);
        foreach ($this->additionalInformationMapping as $item) {
            if (!isset($transaction->$item)) {
                continue;
            }
            $payment->setAdditionalInformation($item, $transaction->$item);
        }
    }

    /**
     * Process 3d secure details
     * @param \Braintree\Transaction $transaction
     * @param \Magento\Sales\Model\Order\Payment $payment
     */
    protected function process3DSecure(Transaction $transaction, Payment $payment)
    {
        if (empty($transaction->threeDSecureInfo)) {
            // remove 3d secure details if they were set previously
            $payment->unsAdditionalInformation(self::LIABILITY_SHIFTED);
            $payment->unsAdditionalInformation(self::LIABILITY_SHIFT_POSSIBLE);
            return;
        }
        /** @var \Braintree\ThreeDSecureInfo $info */
        $info = $transaction->threeDSecureInfo;
        $payment->setAdditionalInformation(self::LIABILITY_SHIFTED, $info->liabilityShifted);
        $payment->setAdditionalInformation(self::LIABILITY_SHIFT_POSSIBLE, $info->liabilityShiftPossible);
    }
}
