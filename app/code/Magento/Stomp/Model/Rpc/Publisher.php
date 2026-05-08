<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Stomp\Model\Rpc;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder;

/**
 * A MessageQueue Publisher to handle publishing a message.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Publisher implements PublisherInterface
{
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
    private $messageEncoder;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * @var ResponseQueueNameBuilder
     */
    private $responseQueueNameBuilder;

    /**
     * @var PublisherConfig
     */
    private $publisherConfig;

    /**
     * Initialize dependencies.
     *
     * @param QueueRepository $queueRepository
     * @param EnvelopeFactory $envelopeFactory
     * @param MessageEncoder|null $messageEncoder
     * @param MessageValidator|null $messageValidator
     * @param ResponseQueueNameBuilder|null $responseQueueNameBuilder
     * @param PublisherConfig|null $publisherConfig
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        QueueRepository $queueRepository,
        EnvelopeFactory $envelopeFactory,
        ?MessageEncoder $messageEncoder = null,
        ?MessageValidator $messageValidator = null,
        ?ResponseQueueNameBuilder $responseQueueNameBuilder = null,
        ?PublisherConfig $publisherConfig = null
    ) {
        $this->queueRepository = $queueRepository;
        $this->envelopeFactory = $envelopeFactory;
        $objectManager = ObjectManager::getInstance();
        $this->messageEncoder = $messageEncoder
            ?? $objectManager->get(MessageEncoder::class);
        $this->messageValidator = $messageValidator
            ?? $objectManager->get(MessageValidator::class);
        $this->responseQueueNameBuilder = $responseQueueNameBuilder
            ?? $objectManager->get(ResponseQueueNameBuilder::class);
        $this->publisherConfig = $publisherConfig
            ?? $objectManager->get(PublisherConfig::class);
    }

    /**
     * @inheritdoc
     */
    public function publish($topicName, $data)
    {
        $this->messageValidator->validate($topicName, $data);
        $data = $this->messageEncoder->encode($topicName, $data);
        $replyTo = $this->responseQueueNameBuilder->getQueueName($topicName);
        $envelope = $this->envelopeFactory->create(
            [
                'body' => $data,
                'properties' => [
                    'reply_to' => $replyTo,
                    'topic_name' => $topicName,
                    'receipt' => rand(),
                    'destination-type' => 'ANYCAST',
                    // md5() here is not for cryptographic use.
                    // phpcs:ignore Magento2.Security.InsecureFunction
                    'message_id' => md5(uniqid($topicName))
                ]
            ]
        );
        $publisher = $this->publisherConfig->getPublisher($topicName);
        $connectionName = $publisher->getConnection()->getName();
        $queue = $this->queueRepository->get($connectionName, $replyTo);
        $responseMessage = $queue->callRpc($envelope);
        return $this->messageEncoder->decode($topicName, $responseMessage, false);
    }
}
