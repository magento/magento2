<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use Magento\Framework\MessageQueue\Bulk\ExchangeRepository;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\AsynchronousOperations\Model\ConfigInterface as AsyncConfig;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\MessageQueue\MessageIdGeneratorInterface;

/**
 * Class MassPublisher used for encoding topic entities to OperationInterface and publish them.
 */
class MassPublisher implements PublisherInterface
{
    /**
     * @var \Magento\Framework\MessageQueue\Bulk\ExchangeRepository
     */
    private $exchangeRepository;

    /**
     * @var \Magento\Framework\MessageQueue\EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var \Magento\Framework\MessageQueue\MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var \Magento\Framework\MessageQueue\MessageValidator
     */
    private $messageValidator;

    /**
     * @var \Magento\Framework\MessageQueue\Publisher\ConfigInterface
     */
    private $publisherConfig;

    /**
     * @var \Magento\Framework\MessageQueue\MessageIdGeneratorInterface
     */
    private $messageIdGenerator;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\MessageQueue\Bulk\ExchangeRepository $exchangeRepository
     * @param \Magento\Framework\MessageQueue\EnvelopeFactory $envelopeFactory
     * @param \Magento\Framework\MessageQueue\MessageEncoder $messageEncoder
     * @param \Magento\Framework\MessageQueue\MessageValidator $messageValidator
     * @param \Magento\Framework\MessageQueue\Publisher\ConfigInterface $publisherConfig
     * @param \Magento\Framework\MessageQueue\MessageIdGeneratorInterface $messageIdGenerator
     */
    public function __construct(
        ExchangeRepository $exchangeRepository,
        EnvelopeFactory $envelopeFactory,
        MessageEncoder $messageEncoder,
        MessageValidator $messageValidator,
        PublisherConfig $publisherConfig,
        MessageIdGeneratorInterface $messageIdGenerator
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
     */
    public function publish($topicName, $data)
    {
        $envelopes = [];
        foreach ($data as $message) {
            $this->messageValidator->validate(AsyncConfig::SYSTEM_TOPIC_NAME, $message);
            $message = $this->messageEncoder->encode(AsyncConfig::SYSTEM_TOPIC_NAME, $message);
            $envelopes[] = $this->envelopeFactory->create(
                [
                    'body' => $message,
                    'properties' => [
                        'topic_name' => $topicName,
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
