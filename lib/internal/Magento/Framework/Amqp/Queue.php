<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Closure;
use Exception;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\Phrase;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Psr\Log\LoggerInterface;

/**
 * @api
 * @since 103.0.0
 */
class Queue implements QueueInterface
{
    /**
     * @var Config
     */
    private $amqpConfig;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The prefetch value is used to specify how many messages that are being sent to the consumer at the same time.
     * @see https://www.rabbitmq.com/consumer-prefetch.html
     * @var int
     */
    private $prefetchCount;

    /**
     * Initialize dependencies.
     *
     * @param Config $amqpConfig
     * @param EnvelopeFactory $envelopeFactory
     * @param string $queueName
     * @param LoggerInterface $logger
     * @param int $prefetchCount
     */
    public function __construct(
        Config $amqpConfig,
        EnvelopeFactory $envelopeFactory,
        $queueName,
        LoggerInterface $logger,
        $prefetchCount = 100
    ) {
        $this->amqpConfig = $amqpConfig;
        $this->queueName = $queueName;
        $this->envelopeFactory = $envelopeFactory;
        $this->logger = $logger;
        $this->prefetchCount = (int)$prefetchCount;
    }

    /**
     * @inheritdoc
     * @since 103.0.0
     */
    public function dequeue()
    {
        $envelope = null;
        $channel = $this->amqpConfig->getChannel();
        // @codingStandardsIgnoreStart
        /** @var AMQPMessage $message */
        try {
            $message = $channel->basic_get($this->queueName);
        } catch (Exception $exception) {
            throw new ConnectionLostException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        if ($message !== null) {
            $properties = array_merge(
                $message->get_properties(),
                [
                    'topic_name' => $message->delivery_info['routing_key'],
                    'delivery_tag' => $message->delivery_info['delivery_tag'],
                ]
            );
            $envelope = $this->envelopeFactory->create(['body' => $message->body, 'properties' => $properties]);
        }

        // @codingStandardsIgnoreEnd
        return $envelope;
    }

    /**
     * @inheritdoc
     * @since 103.0.0
     */
    public function acknowledge(EnvelopeInterface $envelope)
    {
        $properties = $envelope->getProperties();
        $channel = $this->amqpConfig->getChannel();
        // @codingStandardsIgnoreStart
        try {
            $channel->basic_ack($properties['delivery_tag']);
        } catch (Exception $exception) {
            throw new ConnectionLostException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
        // @codingStandardsIgnoreEnd
    }

    /**
     * @inheritdoc
     * @since 103.0.0
     */
    public function subscribe($callback)
    {
        $callbackConverter = function (AMQPMessage $message) use ($callback) {
            // @codingStandardsIgnoreStart
            $properties = array_merge(
                $message->get_properties(),
                [
                    'topic_name' => $message->delivery_info['routing_key'],
                    'delivery_tag' => $message->delivery_info['delivery_tag'],
                ]
            );
            // @codingStandardsIgnoreEnd
            $envelope = $this->envelopeFactory->create(['body' => $message->body, 'properties' => $properties]);

            if ($callback instanceof Closure) {
                $callback($envelope);
            } else {
                call_user_func($callback, $envelope);
            }
        };

        $channel = $this->amqpConfig->getChannel();
        // @codingStandardsIgnoreStart
        $channel->basic_qos(0, $this->prefetchCount, false);
        $channel->basic_consume($this->queueName, '', false, false, false, false, $callbackConverter);
        // @codingStandardsIgnoreEnd
        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }

    /**
     * @inheritdoc
     * @since 103.0.0
     */
    public function reject(EnvelopeInterface $envelope, $requeue = true, $rejectionMessage = null)
    {
        $properties = $envelope->getProperties();

        $channel = $this->amqpConfig->getChannel();
        // @codingStandardsIgnoreStart
        $channel->basic_reject($properties['delivery_tag'], $requeue);
        // @codingStandardsIgnoreEnd
        if ($rejectionMessage !== null) {
            $this->logger->critical(
                new Phrase('Message has been rejected: %message', ['message' => $rejectionMessage])
            );
        }
    }

    /**
     * @inheritdoc
     * @since 103.0.0
     */
    public function push(EnvelopeInterface $envelope)
    {
        $messageProperties = $envelope->getProperties();
        $msg = new AMQPMessage(
            $envelope->getBody(),
            [
                'correlation_id' => $messageProperties['correlation_id'],
                'delivery_mode' => 2
            ]
        );
        $this->amqpConfig->getChannel()->basic_publish($msg, '', $this->queueName);
    }
}
