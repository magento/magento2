<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Rpc;

use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\ExchangeRepository;
use Magento\Framework\Phrase;
use Magento\Framework\MessageQueue\ConfigInterface as MessageQueueConfig;
use PhpAmqpLib\Message\AMQPMessage;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;

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
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * Initialize dependencies.
     *
     * @param ExchangeRepository $exchangeRepository
     * @param EnvelopeFactory $envelopeFactory
     * @param MessageQueueConfig $messageQueueConfig
     * @param \Magento\Amqp\Model\Config $amqpConfig
     * @param MessageEncoder $messageEncoder
     * @param MessageValidator $messageValidator
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
        $this->messageQueueConfig = $messageQueueConfig;
        $this->amqpConfig = $amqpConfig;
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
        $replyTo = $this->messageQueueConfig->getResponseQueueName($topicName);
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
        $connectionName = $this->messageQueueConfig->getConnectionByTopic($topicName);
        $exchange = $this->exchangeRepository->getByConnectionName($connectionName);
        $responseMessage = $exchange->enqueue($topicName, $envelope);
        return $this->messageEncoder->decode($topicName, $responseMessage, false);
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
                'delivery_mode' => 2,
                'message_id' => $messageProperties['message_id']
            ]
        );
        $this->amqpConfig->getChannel()->basic_publish($msg, '', $queue);
    }
}
