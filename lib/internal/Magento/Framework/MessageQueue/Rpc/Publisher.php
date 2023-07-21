<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Rpc;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\ExchangeRepository;
use Magento\Framework\MessageQueue\MessageEncoder;
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ExchangeRepository $exchangeRepository,
        EnvelopeFactory $envelopeFactory,
        $messageQueueConfig = null,
        $amqpConfig = null,
        MessageEncoder $messageEncoder = null,
        MessageValidator $messageValidator = null,
        ResponseQueueNameBuilder $responseQueueNameBuilder = null,
        PublisherConfig $publisherConfig = null
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
                    'delivery_mode' => 2,
                    'correlation_id' => rand(),
                    // md5() here is not for cryptographic use.
                    // phpcs:ignore Magento2.Security.InsecureFunction
                    'message_id' => md5(uniqid($topicName))
                ]
            ]
        );
        $connectionName = $this->publisherConfig->getPublisher($topicName)->getConnection()->getName();
        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);
        $responseMessage = $exchange->enqueue($topicName, $envelope);
        return $this->messageEncoder->decode($topicName, $responseMessage, false);
    }
}
