<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Bulk;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\MessageQueue\Bulk\ExchangeInterface;
use Magento\Framework\MessageQueue\Publisher\ConfigInterface as PublisherConfig;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Used to send messages in bulk in AMQP queue.
 */
class Exchange implements ExchangeInterface
{
    /**
     * @var \Magento\Framework\Amqp\Config
     */
    private $amqpConfig;

    /**
     * @var CommunicationConfigInterface
     */
    private $communicationConfig;

    /**
     * @var PublisherConfig
     */
    private $publisherConfig;

    /**
     * @var \Magento\Framework\Amqp\Exchange
     */
    private $exchange;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Amqp\Config $amqpConfig
     * @param PublisherConfig $publisherConfig
     * @param CommunicationConfigInterface $communicationConfig
     * @param \Magento\Framework\Amqp\Exchange $exchange
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
            // @codingStandardsIgnoreStart
            $msg = new AMQPMessage(
                $envelope->getBody(),
                array_merge(['delivery_mode' => 2], $envelope->getProperties())
            );
            // @codingStandardsIgnoreEnd
            $channel->batch_basic_publish($msg, $exchange, $topic);
        }
        $channel->publish_batch();

        return null;
    }
}
