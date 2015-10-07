<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Amqp\Model;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\ExchangeInterface;
use Magento\Framework\MessageQueue\Config\Data as QueueConfig;
use Magento\Framework\Phrase;
use PhpAmqpLib\Message\AMQPMessage;

class Exchange implements ExchangeInterface
{
    /**
     * @var Config
     */
    private $amqpConfig;

    /**
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * Exchange constructor.
     * @param Config $amqpConfig
     * @param QueueConfig $queueConfig
     */
    public function __construct(Config $amqpConfig, QueueConfig $queueConfig)
    {
        $this->amqpConfig = $amqpConfig;
        $this->queueConfig = $queueConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue($topic, EnvelopeInterface $envelope)
    {
        $channel = $this->amqpConfig->getChannel();
        $exchange = $this->queueConfig->getExchangeByTopic($topic);

        $msg = new AMQPMessage(
            $envelope->getBody(),
            ['delivery_mode' => 2]
        );
        $channel->basic_publish($msg, $exchange, $topic);
    }
}
