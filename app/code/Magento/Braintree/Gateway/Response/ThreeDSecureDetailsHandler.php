<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Response;

use Braintree\Transaction;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Braintree\Gateway\Helper\SubjectReader;
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
     * @var SubjectReader
     */
    private $subjectReader;

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
        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        /** @var Transaction $transaction */
        $transaction = $this->subjectReader->readTransaction($response);

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
        $payment->setAdditionalInformation(self::LIABILITY_SHIFTED, $info->liabilityShifted ? 'Yes' : 'No');
        $shiftPossible = $info->liabilityShiftPossible ? 'Yes' : 'No';
        $payment->setAdditionalInformation(self::LIABILITY_SHIFT_POSSIBLE, $shiftPossible);
    }
}
