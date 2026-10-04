<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\MessageQueue\Rpc;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\ExchangeRepository;
use Magento\Framework\MessageQueue\MessageDeliveryMode;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageIdGeneratorInterface;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * A MessageQueue Publisher to handle publishing a message.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Publisher implements PublisherInterface
{
    /**
     * @var ExchangeRepository
     */
    private $exchangeRepository;

    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

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
     * @var MessageIdGeneratorInterface
     */
    private $messageIdGenerator;

    /**
     * Initialize dependencies.
     *
     * @param ExchangeRepository $exchangeRepository
     * @param EnvelopeFactory $envelopeFactory
     * @param null $messageQueueConfig @deprecated obsolete dependency
     * @param null $amqpConfig @deprecated obsolete dependency
     * @param MessageEncoder|null $messageEncoder
     * @param MessageValidator|null $messageValidator
     * @param ResponseQueueNameBuilder|null $responseQueueNameBuilder
     * @param PublisherConfig|null $publisherConfig
     * @param MessageIdGeneratorInterface|null $messageIdGenerator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ExchangeRepository $exchangeRepository,
        EnvelopeFactory $envelopeFactory,
        $messageQueueConfig = null,
        $amqpConfig = null,
        ?MessageEncoder $messageEncoder = null,
        ?MessageValidator $messageValidator = null,
        ?ResponseQueueNameBuilder $responseQueueNameBuilder = null,
        ?PublisherConfig $publisherConfig = null,
        ?MessageIdGeneratorInterface $messageIdGenerator = null,
    ) {
        $this->exchangeRepository = $exchangeRepository;
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
        $this->messageIdGenerator = $messageIdGenerator
            ?? $objectManager->get(MessageIdGeneratorInterface::class);
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
                    'delivery_mode' => MessageDeliveryMode::PERSISTENT->value,
                    'correlation_id' => rand(),
                    'message_id' => $this->messageIdGenerator->generate($topicName),
                ]
            ]
        );
        $connectionName = $this->publisherConfig->getPublisher($topicName)->getConnection()->getName();
        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);
        $responseMessage = $exchange->enqueue($topicName, $envelope);
        return $this->messageEncoder->decode($topicName, $responseMessage, false);
    }
}
