<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Queue\Consumer;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\SalesRule\Model\Spi\RuleQuoteRecollectTotalsInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Queue consumer for triggering recollect totals by rule ID
 */
class RuleQuoteRecollectTotals
{
    /**
     * @var RuleQuoteRecollectTotalsInterface
     */
    private $ruleQuoteRecollectTotals;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param RuleQuoteRecollectTotalsInterface $ruleQuoteRecollectTotals
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param EntityManager $entityManager
     */
    public function __construct(
        RuleQuoteRecollectTotalsInterface $ruleQuoteRecollectTotals,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        EntityManager $entityManager
    ) {
        $this->ruleQuoteRecollectTotals = $ruleQuoteRecollectTotals;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * Process coupon usage update
     *
     * @param OperationInterface $operation
     * @return void
     * @throws \Exception
     */
    public function process(OperationInterface $operation): void
    {
        try {
            $serializedData = $operation->getSerializedData();
            $data = $this->serializer->unserialize($serializedData);
            $this->ruleQuoteRecollectTotals->execute($data['rule_id']);
        } catch (LockWaitException  $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = __($e->getMessage());
        } catch (DeadlockException  $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = __($e->getMessage());
        } catch (ConnectionException  $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = __($e->getMessage());
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = __(
                'Sorry, something went wrong while triggering recollect totals for affected quotes.' .
                ' Please see log for details.'
            );
        }

        $operation->setStatus($status ?? OperationInterface::STATUS_TYPE_COMPLETE)
            ->setErrorCode($errorCode ?? null)
            ->setResultMessage($message ?? null);

        $this->entityManager->save($operation);
    }
}
