<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Bulk;

use Magento\Framework\MessageQueue\Bulk\ExchangeInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;

/**
 * Used to send messages in bulk in AMQP queue.
 * @since 2.2.0
 */
class Exchange implements ExchangeInterface
{
    /**
     * @var \Magento\Framework\Amqp\Config
     * @since 2.2.0
     */
    private $amqpConfig;

    /**
     * @var CommunicationConfigInterface
     * @since 2.2.0
     */
    private $communicationConfig;

    /**
     * @var PublisherConfig
     * @since 2.2.0
     */
    private $publisherConfig;

    /**
     * @var \Magento\Framework\Amqp\Exchange
     * @since 2.2.0
     */
    private $exchange;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Amqp\Config $amqpConfig
     * @param PublisherConfig $publisherConfig
     * @param CommunicationConfigInterface $communicationConfig
     * @param \Magento\Framework\Amqp\Exchange $exchange
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Framework\Amqp\Config $amqpConfig,
        PublisherConfig $publisherConfig,
        CommunicationConfigInterface $communicationConfig,
        \Magento\Framework\Amqp\Exchange $exchange
    ) {
        $this->amqpConfig = $amqpConfig;
        $this->communicationConfig = $communicationConfig;
        $this->publisherConfig = $publisherConfig;
        $this->exchange = $exchange;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function enqueue($topic, array $envelopes)
    {
        $topicData = $this->communicationConfig->getTopic($topic);
        $isSync = $topicData[CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS];

        if ($isSync) {
            $responses = [];
            foreach ($envelopes as $envelope) {
                $responses[] = $this->exchange->enqueue($topic, $envelope);
            }
            return $responses;
        }

        $channel = $this->amqpConfig->getChannel();
        $publisher = $this->publisherConfig->getPublisher($topic);
        $exchange = $publisher->getConnection()->getExchange();

        foreach ($envelopes as $envelope) {
            $msg = new AMQPMessage($envelope->getBody(), $envelope->getProperties());
            $channel->batch_basic_publish($msg, $exchange, $topic);
        }
        $channel->publish_batch();

        return null;
    }
}
