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
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Class ThreeDSecureDetailsHandler
 */
class ThreeDSecureDetailsHandler implements HandlerInterface
{
    const LIABILITY_SHIFTED = 'liabilityShifted';

    const LIABILITY_SHIFT_POSSIBLE = 'liabilityShiftPossible';

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        /** @var Transaction $transaction */
        $transaction = $response['object']->transaction;

        if ($payment->hasAdditionalInformation(self::LIABILITY_SHIFTED)) {
            // remove 3d secure details for reorder
            $payment->unsAdditionalInformation(self::LIABILITY_SHIFTED);
            $payment->unsAdditionalInformation(self::LIABILITY_SHIFT_POSSIBLE);
        }

        if (empty($transaction->threeDSecureInfo)) {
            return;
        }

        /** @var \Braintree\ThreeDSecureInfo $info */
        $info = $transaction->threeDSecureInfo;
        $payment->setAdditionalInformation(self::LIABILITY_SHIFTED, $info->liabilityShifted);
        $payment->setAdditionalInformation(self::LIABILITY_SHIFT_POSSIBLE, $info->liabilityShiftPossible);
    }
}