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
 * Processes payment information from a void transaction response
 */
class PaymentReviewStatusHandler implements HandlerInterface
{
    /**
     * @var array
     */
    private static $reviewPendingStatuses = [
        'FDSPendingReview',
        'FDSAuthorizedPendingReview',
    ];

    /**
     * @var array
     */
    private static $reviewDeclinedStatuses = [
        'void',
        'declined',
    ];

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
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        if ($payment instanceof Payment) {
            $paymentDO = $this->subjectReader->readPayment($handlingSubject);
            $payment = $paymentDO->getPayment();

            $status = $response['transaction']['transactionStatus'];
            // This data is only used when updating the order payment via Get Payment Update
            if (!in_array($status, self::$reviewPendingStatuses)) {
                $denied = in_array($status, self::$reviewDeclinedStatuses);
                $payment->setData('is_transaction_denied', $denied);
                $payment->setData('is_transaction_approved', !$denied);
            }
        }
    }
}
