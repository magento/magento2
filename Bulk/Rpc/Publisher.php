<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Bulk\Rpc;

use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\Bulk\ExchangeRepository;
use PhpAmqpLib\Message\AMQPMessage;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder;

/**
 * A MessageQueue Publisher to handle publishing a message.
 * @since 2.2.0
 */
class Publisher implements PublisherInterface
{
    /**
     * @var ExchangeRepository
     * @since 2.2.0
     */
    private $exchangeRepository;

    /**
     * @var EnvelopeFactory
     * @since 2.2.0
     */
    private $envelopeFactory;

    /**
     * @var MessageEncoder
     * @since 2.2.0
     */
    private $messageEncoder;

    /**
     * @var MessageValidator
     * @since 2.2.0
     */
    private $messageValidator;

    /**
     * @var ResponseQueueNameBuilder
     * @since 2.2.0
     */
    private $responseQueueNameBuilder;

    /**
     * @var PublisherConfig
     * @since 2.2.0
     */
    private $publisherConfig;

    /**
     * @var \Magento\Framework\MessageQueue\MessageIdGeneratorInterface
     * @since 2.2.0
     */
    private $messageIdGenerator;

    /**
     * @param ExchangeRepository $exchangeRepository
     * @param EnvelopeFactory $envelopeFactory
     * @param MessageEncoder $messageEncoder
     * @param MessageValidator $messageValidator
     * @param ResponseQueueNameBuilder $responseQueueNameBuilder
     * @param PublisherConfig $publisherConfig
     * @param \Magento\Framework\MessageQueue\MessageIdGeneratorInterface $messageIdGenerator
     * @since 2.2.0
     */
    public function __construct(
        ExchangeRepository $exchangeRepository,
        EnvelopeFactory $envelopeFactory,
        MessageEncoder $messageEncoder,
        MessageValidator $messageValidator,
        ResponseQueueNameBuilder $responseQueueNameBuilder,
        PublisherConfig $publisherConfig,
        \Magento\Framework\MessageQueue\MessageIdGeneratorInterface $messageIdGenerator
    ) {
        $this->exchangeRepository = $exchangeRepository;
        $this->envelopeFactory = $envelopeFactory;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
        $this->responseQueueNameBuilder = $responseQueueNameBuilder;
        $this->publisherConfig = $publisherConfig;
        $this->messageIdGenerator = $messageIdGenerator;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function publish($topicName, $data)
    {
        $envelopes = [];
        $replyTo = $this->responseQueueNameBuilder->getQueueName($topicName);
        foreach ($data as $message) {
            $this->messageValidator->validate($topicName, $message);
            $message = $this->messageEncoder->encode($topicName, $message);
            $envelope = $this->envelopeFactory->create(
                [
                    'body' => $message,
                    'properties' => [
                        'reply_to' => $replyTo,
                        'delivery_mode' => 2,
                        'correlation_id' => rand(),
                        'message_id' => $this->messageIdGenerator->generate($topicName),
                    ]
                ]
            );
            $envelopes[] = $envelope;
        }
        $publisher = $this->publisherConfig->getPublisher($topicName);
        $connectionName = $publisher->getConnection()->getName();
        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);
        return $exchange->enqueue($topicName, $envelopes);
    }
}
