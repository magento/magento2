<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Amqp\Model;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\ExchangeInterface;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
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
     *
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
        $correlationId = $envelope->getProperties()['correlation_id'];
        $responseBody = null;
        /** @var AMQPMessage $response */
        $callback = function ($response) use ($correlationId, &$responseBody, $channel) {
            if ($response->get('correlation_id') == $correlationId) {
                $responseBody = $response->body;
                $channel->basic_ack($response->get('delivery_tag'));
            }
        };
        if ($envelope->getProperties()['reply_to']) {
            $replyTo = $envelope->getProperties()['reply_to'];
        } else {
            $replyTo = to_snake_case($topic) . '.response';
        }
        $channel->basic_consume(
            $replyTo,
            '',
            false,
            false,
            false,
            false,
            $callback
        );
        $msg = new AMQPMessage(
            $envelope->getBody(),
            $envelope->getProperties()
        );
        $channel->basic_publish($msg, $exchange, $topic);

        // TODO: add ability to timeout
        while ($responseBody === null) {
            $channel->wait();
        }
        $channel->close();
        return $responseBody;
    }
}
