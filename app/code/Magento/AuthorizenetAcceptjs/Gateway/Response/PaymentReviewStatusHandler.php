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
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class PaymentReviewStatusHandler implements HandlerInterface
{
    private const REVIEW_PENDING_STATUSES = [
        'FDSPendingReview',
        'FDSAuthorizedPendingReview'
    ];
    private const REVIEW_DECLINED_STATUSES = [
        'void',
        'declined'
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
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        if ($payment instanceof Payment) {
            $paymentDO = $this->subjectReader->readPayment($handlingSubject);
            $payment = $paymentDO->getPayment();

            $status = $response['transaction']['transactionStatus'];
            // This data is only used when updating the order payment via Get Payment Update
            if (!in_array($status, self::REVIEW_PENDING_STATUSES)) {
                $denied = in_array($status, self::REVIEW_DECLINED_STATUSES);
                $payment->setData('is_transaction_denied', $denied);
                $payment->setData('is_transaction_approved', !$denied);
            }
        }
    }
}
