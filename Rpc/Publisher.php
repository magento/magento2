<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Rpc;

use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\ExchangeRepository;
use Magento\Framework\MessageQueue\ConfigInterface as MessageQueueConfig;
use PhpAmqpLib\Message\AMQPMessage;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder;
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

    /**
     * Initialize dependencies.
     *
     * @param ExchangeRepository $exchangeRepository
     * @param EnvelopeFactory $envelopeFactory
     * @param MessageQueueConfig $messageQueueConfig
     * @param \Magento\Amqp\Model\Config $amqpConfig
     * @param MessageEncoder $messageEncoder
     * @param MessageValidator $messageValidator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ExchangeRepository $exchangeRepository,
        EnvelopeFactory $envelopeFactory,
        MessageQueueConfig $messageQueueConfig,
        \Magento\Amqp\Model\Config $amqpConfig,
        MessageEncoder $messageEncoder,
        MessageValidator $messageValidator
    ) {
        $this->exchangeRepository = $exchangeRepository;
        $this->envelopeFactory = $envelopeFactory;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
    }

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
     * @deprecated
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
     * @deprecated
     */
    private function getPublisherConfig()
    {
        if ($this->publisherConfig === null) {
            $this->publisherConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(PublisherConfig::class);
        }
        return $this->publisherConfig;
    }
}
