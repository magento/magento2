<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Amqp\Model;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\Config\Data as AmqpConfig;
use PhpAmqpLib\Message\AMQPMessage;
use Magento\Framework\MessageQueue\EnvelopeFactory;

/**
 * Class Queue
 */
class Queue implements QueueInterface
{
    /**
     * @var Config
     */
    private $amqpConfig;

    /**
     * @var AmqpConfig
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
     * Initialize dependencies.
     *
     * @param Config $amqpConfig
     * @param AmqpConfig $amqpConfig
     * @param EnvelopeFactory $envelopeFactory
     * @param string $queueName
     */
    public function __construct(
        Config $amqpConfig,
        AmqpConfig $amqpConfig,
        EnvelopeFactory $envelopeFactory,
        $queueName
    ) {
        $this->amqpConfig = $amqpConfig;
        $this->amqpConfig = $amqpConfig;
        $this->queueName = $queueName;
        $this->envelopeFactory = $envelopeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        $envelope = null;
        $channel = $this->amqpConfig->getChannel();
        // @codingStandardsIgnoreStart
        /** @var \PhpAmqpLib\Message\AMQPMessage $message */
        try {
            $message = $channel->basic_get($this->queueName);
        } catch(\PhpAmqpLib\Exception\AMQPProtocolConnectionException $e) {
            throw new \Magento\Framework\MessageQueue\ConnectionLostException(
                $e->getMessage(),
                $e->getCode(),
                $e
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
     * {@inheritdoc}
     */
    public function acknowledge(EnvelopeInterface $envelope)
    {
        $properties = $envelope->getProperties();
        $channel = $this->amqpConfig->getChannel();
        // @codingStandardsIgnoreStart
        try {
            $channel->basic_ack($properties['delivery_tag']);
        } catch(\PhpAmqpLib\Exception\AMQPProtocolConnectionException $e) {
            throw new \Magento\Framework\MessageQueue\ConnectionLostException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        // @codingStandardsIgnoreEnd
    }

    /**
     * {@inheritdoc}
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

            try {
                call_user_func($callback, $envelope);
                $this->acknowledge($envelope);
            } catch (\Exception $e) {
                $this->reject($envelope);
            }
        };

        $channel = $this->amqpConfig->getChannel();
        // @codingStandardsIgnoreStart
        $channel->basic_consume($this->queueName, '', false, false, false, false, $callbackConverter);
        // @codingStandardsIgnoreEnd
        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }

    /**
     * (@inheritdoc)
     */
    public function reject(EnvelopeInterface $envelope)
    {
        $properties = $envelope->getProperties();

        $channel = $this->amqpConfig->getChannel();
        // @codingStandardsIgnoreStart
        $channel->basic_reject($properties['delivery_tag'], true);
        // @codingStandardsIgnoreEnd
    }
}
