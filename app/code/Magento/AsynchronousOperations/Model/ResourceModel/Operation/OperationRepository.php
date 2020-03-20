<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model\ResourceModel\Operation;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Model\OperationRepositoryInterface;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\EntityManager\EntityManager;

/**
 * Create operation for list of bulk operations.
 */
class OperationRepository implements OperationRepositoryInterface
{
    /**
     * @var OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * @param OperationInterfaceFactory $operationFactory
     * @param EntityManager $entityManager
     * @param MessageValidator $messageValidator
     * @param MessageEncoder $messageEncoder
     * @param Json $jsonSerializer
     */
    public function __construct(
        OperationInterfaceFactory $operationFactory,
        EntityManager $entityManager,
        MessageValidator $messageValidator,
        MessageEncoder $messageEncoder,
        Json $jsonSerializer
    ) {
        $this->operationFactory = $operationFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
        $this->entityManager = $entityManager;
    }

    /**
     * Create operation by topic, parameters and group ID
     *
     * @param string $topicName
     * @param array $entityParams
     * @param string $groupId
     * @return OperationInterface
     * @deprecated No longer used.
     * @see create()
     */
    public function createByTopic($topicName, $entityParams, $groupId)
    {
        $this->messageValidator->validate($topicName, $entityParams);
        $encodedMessage = $this->messageEncoder->encode($topicName, $entityParams);

        $serializedData = [
            'entity_id'        => null,
            'entity_link'      => '',
            'meta_information' => $encodedMessage,
        ];
        $data = [
            'data' => [
                OperationInterface::BULK_ID         => $groupId,
                OperationInterface::TOPIC_NAME      => $topicName,
                OperationInterface::SERIALIZED_DATA => $this->jsonSerializer->serialize($serializedData),
                OperationInterface::STATUS          => OperationInterface::STATUS_TYPE_OPEN,
            ],
        ];

        /** @var OperationInterface $operation */
        $operation = $this->operationFactory->create($data);
        return $this->entityManager->save($operation);
    }

    /**
     * @inheritDoc
     */
    public function create($topicName, $entityParams, $groupId, $operationId): OperationInterface
    {
        return $this->createByTopic($topicName, $entityParams, $groupId);
    }
}
