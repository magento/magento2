<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Stomp\Model\Bulk;

use Magento\Framework\MessageQueue\Bulk\Queue\QueueRepository as BulkQueueRepository;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageIdGeneratorInterface;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\MessageQueue\QueueRepository;

/**
 * A MessageQueue Publisher to handle publishing messages in bulk.
 */
class Publisher implements PublisherInterface
{
    /**
     * @var BulkQueueRepository
     */
    private BulkQueueRepository $bulkQueueRepository;

    /**
     * @var QueueRepository
     */
    private QueueRepository $queueRepository;

    /**
     * @var EnvelopeFactory
     */
    private EnvelopeFactory $envelopeFactory;

    /**
     * @var MessageEncoder
     */
    private MessageEncoder $messageEncoder;

    /**
     * @var MessageValidator
     */
    private MessageValidator $messageValidator;

    /**
     * @var PublisherConfig
     */
    private PublisherConfig $publisherConfig;

    /**
     * @var MessageIdGeneratorInterface
     */
    private MessageIdGeneratorInterface $messageIdGenerator;

    /**
     * @param QueueRepository $queueRepository
     * @param BulkQueueRepository $bulkQueueRepository
     * @param EnvelopeFactory $envelopeFactory
     * @param MessageEncoder $messageEncoder
     * @param MessageValidator $messageValidator
     * @param PublisherConfig $publisherConfig
     * @param MessageIdGeneratorInterface $messageIdGenerator
     */
    public function __construct(
        QueueRepository $queueRepository,
        BulkQueueRepository $bulkQueueRepository,
        EnvelopeFactory $envelopeFactory,
        MessageEncoder $messageEncoder,
        MessageValidator $messageValidator,
        PublisherConfig $publisherConfig,
        MessageIdGeneratorInterface $messageIdGenerator
    ) {
        $this->queueRepository = $queueRepository;
        $this->bulkQueueRepository = $bulkQueueRepository;
        $this->envelopeFactory = $envelopeFactory;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
        $this->publisherConfig = $publisherConfig;
        $this->messageIdGenerator = $messageIdGenerator;
    }

    /**
     * @inheritdoc
     */
    public function publish($topicName, $data)
    {
        $envelopes = [];
        foreach ($data as $message) {
            $this->messageValidator->validate($topicName, $message);
            $message = $this->messageEncoder->encode($topicName, $message);
            $envelopes[] = $this->envelopeFactory->create(
                [
                    'body' => $message,
                    'properties' => [
                        'topic_name' => $topicName,
                        'persistent' => 'true',
                        'message_id' => $this->messageIdGenerator->generate($topicName),
                    ]
                ]
            );
        }

        $publisher = $this->publisherConfig->getPublisher($topicName);
        $connectionName = $publisher->getConnection()->getName();
        $queueName = $publisher->getQueue() ? $publisher->getQueue(): $topicName;
        $queue = $this->queueRepository->get($connectionName, $queueName);
        $bulkQueue = $this->bulkQueueRepository->get($connectionName, $queueName);
        $bulkQueue->push($queue, $topicName, $envelopes);

        return null;
    }
}
