<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Bulk;

use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;

/**
 * A MessageQueue Publisher to handle publishing messages in bulk.
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
     * @param PublisherConfig $publisherConfig
     * @param \Magento\Framework\MessageQueue\MessageIdGeneratorInterface $messageIdGenerator
     * @since 2.2.0
     */
    public function __construct(
        ExchangeRepository $exchangeRepository,
        EnvelopeFactory $envelopeFactory,
        MessageEncoder $messageEncoder,
        MessageValidator $messageValidator,
        PublisherConfig $publisherConfig,
        \Magento\Framework\MessageQueue\MessageIdGeneratorInterface $messageIdGenerator
    ) {
        $this->exchangeRepository = $exchangeRepository;
        $this->envelopeFactory = $envelopeFactory;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
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
        foreach ($data as $message) {
            $this->messageValidator->validate($topicName, $message);
            $message = $this->messageEncoder->encode($topicName, $message);
            $envelopes[] = $this->envelopeFactory->create(
                [
                    'body' => $message,
                    'properties' => [
                        'delivery_mode' => 2,
                        'message_id' => $this->messageIdGenerator->generate($topicName),
                    ]
                ]
            );
        }
        $publisher = $this->publisherConfig->getPublisher($topicName);
        $connectionName = $publisher->getConnection()->getName();
        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);
        $exchange->enqueue($topicName, $envelopes);
        return null;
    }
}
