<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\ConfigInterface as AsyncConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Throwable;

/**
 * Decorator for MessageController
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MessageControllerDecorator
{
    /**
     * Retry transient failures on updating {@see OperationInterface} metadata during lock
     */
    private const MAX_LOCK_TRANSACTION_ATTEMPTS = 5;

    /**
     * Delay between retries in microseconds.
     */
    private const LOCK_TRANSACTION_RETRY_DELAY_MICROSECONDS = 50000;

    /**
     * @var MessageController
     */
    private $messageController;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param ResourceConnection $resource
     * @param MessageController $messageController
     * @param MessageValidator $messageValidator
     * @param MessageEncoder $messageEncoder
     * @param MetadataPool $metadataPool
     * @param DateTime $dateTime
     */
    public function __construct(
        ResourceConnection $resource,
        MessageController $messageController,
        MessageValidator $messageValidator,
        MessageEncoder $messageEncoder,
        MetadataPool $metadataPool,
        DateTime $dateTime
    ) {
        $this->messageController = $messageController;
        $this->resource = $resource;
        $this->messageValidator = $messageValidator;
        $this->messageEncoder = $messageEncoder;
        $this->metadataPool = $metadataPool;
        $this->dateTime = $dateTime;
    }

    /**
     * Creates lock for provided message and update the operation start time
     *
     * @param EnvelopeInterface $envelope
     * @param string $consumerName
     * @return LockInterface
     */
    public function lock(EnvelopeInterface $envelope, string $consumerName): LockInterface
    {
        $operation = $this->messageEncoder->decode(AsyncConfig::SYSTEM_TOPIC_NAME, $envelope->getBody());
        $this->messageValidator->validate(AsyncConfig::SYSTEM_TOPIC_NAME, $operation);
        $metadata = $this->metadataPool->getMetadata(OperationInterface::class);
        $connection = $this->resource->getConnection($metadata->getEntityConnectionName());

        for ($attempt = 1; $attempt <= self::MAX_LOCK_TRANSACTION_ATTEMPTS; $attempt++) {
            $connection->beginTransaction();
            try {
                $lock = $this->messageController->lock($envelope, $consumerName);
                $connection->update(
                    $metadata->getEntityTable(),
                    [
                        'started_at' => $connection->formatDate($this->dateTime->gmtTimestamp())
                    ],
                    [
                        'bulk_uuid = ?' => $operation->getBulkUuid(),
                        'operation_key = ?' => $operation->getId()
                    ]
                );
                $connection->commit();

                return $lock;
            } catch (Throwable $exception) {
                $connection->rollBack();
                if ($this->isTransientBulkTransactionFailure($exception)
                    && $attempt < self::MAX_LOCK_TRANSACTION_ATTEMPTS
                ) {
                    usleep(self::LOCK_TRANSACTION_RETRY_DELAY_MICROSECONDS);
                    continue;
                }
                throw $exception;
            }
        }

        throw new \LogicException('Unable to lock consumer message.');
    }

    /**
     * Whether the failure may succeed after rolling back and retrying the transaction.
     *
     * @param Throwable $e
     * @return bool
     */
    private function isTransientBulkTransactionFailure(Throwable $e): bool
    {
        if ($e instanceof DeadlockException || $e instanceof LockWaitException) {
            return true;
        }
        $current = $e;
        while ($current !== null) {
            if ($current instanceof \PDOException) {
                $driverCode = isset($current->errorInfo[1]) ? (int)$current->errorInfo[1] : 0;
                if (in_array($driverCode, [1020, 1213, 1205], true)) {
                    return true;
                }
            }
            if (str_contains($current->getMessage(), 'Record has changed since last read')) {
                return true;
            }
            $current = $current->getPrevious();
        }

        return false;
    }
}
