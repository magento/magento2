<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Command\CommandPool;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Syncs the transaction status with authorize.net
 */
class FetchTransactionInfoCommand implements CommandInterface
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
     * @var CommandPool
     */
    private $commandPool;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param CommandPoolInterface $commandPool
     * @param SubjectReader $subjectReader
     * @param Config $config
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        SubjectReader $subjectReader,
        Config $config
    ) {
        $this->commandPool = $commandPool;
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();

        if (!$payment instanceof Payment) {
            return [];
        }

        $command = $this->commandPool->get('get_transaction_details');
        $result = $command->execute($commandSubject);
        $response = $result->get();
        $status = $response['transaction']['transactionStatus'];

        // This data is only used when updating the payment on the order
        if (!in_array($status, self::REVIEW_PENDING_STATUSES)) {
            $denied = in_array($status, self::REVIEW_DECLINED_STATUSES);
            $payment->setData('is_transaction_denied', $denied);
            $payment->setData('is_transaction_approved', !$denied);
        }

        $additionalInformationKeys = $this->config->getTransactionInfoSyncKeys($order->getStoreId());
        $rawDetails = [];
        foreach ($additionalInformationKeys as $key) {
            if (isset($response['transaction'][$key])) {
                $rawDetails[$key] = $response['transaction'][$key];
            }
        }

        return $rawDetails;
    }
}
