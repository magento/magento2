<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronization\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Publish media content synchronization queue.
 */
class Publish
{
    /**
     * Media content synchronization queue topic name.
     */
    private const TOPIC_MEDIA_CONTENT_SYNCHRONIZATION = 'media.content.synchronization';

    /**
     * @var OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param OperationInterfaceFactory $operationFactory
     * @param IdentityGeneratorInterface $identityService
     * @param PublisherInterface $publisher
     * @param SerializerInterface $serializer
     */
    public function __construct(
        OperationInterfaceFactory $operationFactory,
        IdentityGeneratorInterface $identityService,
        PublisherInterface $publisher,
        SerializerInterface $serializer
    ) {
        $this->operationFactory = $operationFactory;
        $this->identityService = $identityService;
        $this->serializer = $serializer;
        $this->publisher = $publisher;
    }

    /**
     * Publish media content synchronization message to the message queue
     *
     * @param array $contentIdentities
     */
    public function execute(array $contentIdentities = []) : void
    {
        $data = [
            'data' => [
                'bulk_uuid' => $this->identityService->generateId(),
                'topic_name' => self::TOPIC_MEDIA_CONTENT_SYNCHRONIZATION,
                'serialized_data' => $this->serializer->serialize($contentIdentities),
                'status' => OperationInterface::STATUS_TYPE_OPEN,
            ]
        ];
        $operation = $this->operationFactory->create($data);

        $this->publisher->publish(
            self::TOPIC_MEDIA_CONTENT_SYNCHRONIZATION,
            $operation
        );
    }
}
