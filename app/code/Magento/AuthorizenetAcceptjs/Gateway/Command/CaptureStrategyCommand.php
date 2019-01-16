<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AuthorizenetAcceptjs\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Class CaptureStrategyCommand
 * @SuppressWarnings(PHPMD)
 */
class CaptureStrategyCommand implements CommandInterface
{
    const AUTHORIZE = 'authorize';
    const CAPTURE = 'settle';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param CommandPoolInterface $commandPool
     * @param TransactionRepositoryInterface $repository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        TransactionRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubjectReader $subjectReader
    ) {
        $this->commandPool = $commandPool;
        $this->transactionRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        $command = $this->getCommand($paymentDO);
        $this->commandPool->get($command)->execute($commandSubject);
    }

    /**
     * Get execution command name.
     *
     * @param PaymentDataObjectInterface $paymentDO
     * @return string
     */
    private function getCommand(PaymentDataObjectInterface $paymentDO)
    {
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        // If auth transaction does not exist then execute authorize&capture command
        $existsCapture = $this->captureTransactionExists($payment);
        if (!$payment->getAuthorizationTransaction() && !$existsCapture) {
            return self::AUTHORIZE;
        }

        return self::CAPTURE;
    }

    /**
     * Check if capture transaction already exists
     *
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    private function captureTransactionExists(OrderPaymentInterface $payment)
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('payment_id')
                    ->setValue($payment->getId())
                    ->create(),
            ]
        );

        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('txn_type')
                    ->setValue(TransactionInterface::TYPE_CAPTURE)
                    ->create(),
            ]
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $count = $this->transactionRepository->getList($searchCriteria)->getTotalCount();

        return $count > 0;
    }
}
