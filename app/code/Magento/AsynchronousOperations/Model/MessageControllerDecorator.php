<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\ConfigInterface as AsyncConfig;
use Magento\Framework\App\ResourceConnection;
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
 */
class MessageControllerDecorator
{
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
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }

        return $lock;
    }
}
