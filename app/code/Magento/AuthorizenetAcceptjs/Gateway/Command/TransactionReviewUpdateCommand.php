<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Command;


use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Syncs the transaction status with authorize.net
 */
class TransactionReviewUpdateCommand implements CommandInterface
{
    private const REVIEW_PENDING_STATUSES = [
        'FDSPendingReview',
        'FDSAuthorizedPendingReview'
    ];
    private const STATUS_REVIEW_DECLINED = 'void';

    /**
     * @var CommandPool
     */
    private $commandPool;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param CommandPoolInterface $commandPool
     * @param SubjectReader $subjectReader
     */
    public function __construct(CommandPoolInterface $commandPool, SubjectReader $subjectReader)
    {
        $this->commandPool = $commandPool;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject): void
    {
        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        $payment = $paymentDO->getPayment();

        if (!$payment instanceof Payment) {
            return;
        }

        $command = $this->commandPool->get('get_transaction_details');
        $result = $command->execute($commandSubject);
        $response = $result->get();
        $status = $response['transaction']['transactionStatus'];

        if (!in_array($status, self::REVIEW_PENDING_STATUSES)) {
            $denied = ($status === self::STATUS_REVIEW_DECLINED);
            $payment->setData('is_transaction_denied', $denied);
            $payment->setData('is_transaction_approved', !$denied);
        }
    }
}
