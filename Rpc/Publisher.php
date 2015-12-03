<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Rpc;

use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\ExchangeRepository;
use Magento\Framework\Phrase;
use Magento\Framework\MessageQueue\ConfigInterface as MessageQueueConfig;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * A MessageQueue Publisher to handle publishing a message.
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
     * @var MessageQueueConfig
     */
    private $messageQueueConfig;

    /**
     * @var \Magento\Amqp\Model\Config
     */
    private $amqpConfig;


    /**
     * Initialize dependencies.
     *
     * @param ExchangeRepository $exchangeRepository
     * @param EnvelopeFactory $envelopeFactory
     * @param MessageQueueConfig $messageQueueConfig
     * @param \Magento\Amqp\Model\Config $amqpConfig
     */
    public function __construct(
        ExchangeRepository $exchangeRepository,
        EnvelopeFactory $envelopeFactory,
        MessageQueueConfig $messageQueueConfig,
        \Magento\Amqp\Model\Config $amqpConfig
    ) {
        $this->exchangeRepository = $exchangeRepository;
        $this->envelopeFactory = $envelopeFactory;
        $this->messageQueueConfig = $messageQueueConfig;
        $this->amqpConfig = $amqpConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($topicName, $data)
    {
        $replyTo = $this->messageQueueConfig->getResponseQueueName($topicName);
        $envelope = $this->envelopeFactory->create(
            [
                'body' => $data,
                'properties' => [
                    'reply_to' => $replyTo,
                    'delivery_mode' => 2,
                    'correlation_id' => rand()
                ]
            ]
        );
        $connectionName = $this->messageQueueConfig->getConnectionByTopic($topicName);
        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);
        return $exchange->enqueue($topicName, $envelope);
    }

    /**
     * @inheritDoc
     */
    public function publishToQueue(EnvelopeInterface $message, $data, $queue)
    {
        $messageProperties = $message->getProperties();
        $msg = new AMQPMessage(
            $data,
            [
                'correlation_id' => $messageProperties['correlation_id'],
                'delivery_mode' => 2
            ]
        );
        $this->amqpConfig->getChannel()->basic_publish($msg, '', $queue);
    }
}
