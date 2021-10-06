<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Rpc;

use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\ExchangeRepository;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;

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

    //@codingStandardsIgnoreStart
    /**
     * Initialize dependencies.
     *
     * @param ExchangeRepository $exchangeRepository
     * @param EnvelopeFactory $envelopeFactory
     * @param null $messageQueueConfig @deprecated obsolete dependency
     * @param null $amqpConfig @deprecated obsolete dependency
     * @param MessageEncoder $messageEncoder
     * @param MessageValidator $messageValidator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ExchangeRepository $exchangeRepository,
        EnvelopeFactory $envelopeFactory,
        $messageQueueConfig = null,
        $amqpConfig = null,
        MessageEncoder $messageEncoder,
        MessageValidator $messageValidator
    ) {
        $this->exchangeRepository = $exchangeRepository;
        $this->envelopeFactory = $envelopeFactory;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
    }
    //@codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function publish($topicName, $data)
    {
        $this->messageValidator->validate($topicName, $data);
        $data = $this->messageEncoder->encode($topicName, $data);
        $replyTo = $this->getResponseQueueNameBuilder()->getQueueName($topicName);
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
        $connectionName = $this->getPublisherConfig()->getPublisher($topicName)->getConnection()->getName();
        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);
        $responseMessage = $exchange->enqueue($topicName, $envelope);
        return $this->messageEncoder->decode($topicName, $responseMessage, false);
    }

    /**
     * Get response queue name builder.
     *
     * @return ResponseQueueNameBuilder
     *
     * @deprecated 103.0.0
     */
    private function getResponseQueueNameBuilder()
    {
        if ($this->responseQueueNameBuilder === null) {
            $this->responseQueueNameBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(ResponseQueueNameBuilder::class);
        }
        return $this->responseQueueNameBuilder;
    }

    /**
     * Get publisher config.
     *
     * @return PublisherConfig
     *
     * @deprecated 103.0.0
     */
    private function getPublisherConfig()
    {
        if ($this->publisherConfig === null) {
            $this->publisherConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(PublisherConfig::class);
        }
        return $this->publisherConfig;
    }
}
