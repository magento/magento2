<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Amqp\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\ExchangeInterface;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Phrase;
use PhpAmqpLib\Message\AMQPMessage;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;

class Exchange implements ExchangeInterface
{
    const RPC_CONNECTION_TIMEOUT = 30;

    /**
     * @var Config
     */
    private $amqpConfig;

    /**
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * @var CommunicationConfigInterface
     */
    private $communicationConfig;

    /**
     * @var int
     */
    private $rpcConnectionTimeout;

    /**
     * Initialize dependencies.
     *
     * @param Config $amqpConfig
     * @param QueueConfig $queueConfig
     * @param CommunicationConfigInterface $communicationConfig
     * @param int $rpcConnectionTimeout
     */
    public function __construct(
        Config $amqpConfig,
        QueueConfig $queueConfig,
        CommunicationConfigInterface $communicationConfig,
        $rpcConnectionTimeout = self::RPC_CONNECTION_TIMEOUT
    ) {
        $this->amqpConfig = $amqpConfig;
        $this->queueConfig = $queueConfig;
        $this->communicationConfig = $communicationConfig;
        $this->rpcConnectionTimeout = $rpcConnectionTimeout;
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue($topic, EnvelopeInterface $envelope)
    {
        $topicData = $this->communicationConfig->getTopic($topic);
        $isSync = $topicData[CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS];

        $channel = $this->amqpConfig->getChannel();
        $exchange = $this->queueConfig->getExchangeByTopic($topic);
        $responseBody = null;

        $msg = new AMQPMessage($envelope->getBody(), $envelope->getProperties());
        if ($isSync) {
            $correlationId = $envelope->getProperties()['correlation_id'];
            /** @var AMQPMessage $response */
            $callback = function ($response) use ($correlationId, &$responseBody, $channel) {
                if ($response->get('correlation_id') == $correlationId) {
                    $responseBody = $response->body;
                    $channel->basic_ack($response->get('delivery_tag'));
                } else {
                    //push message back to the queue
                    $channel->basic_reject($response->get('delivery_tag'), true);
                }
            };
            if ($envelope->getProperties()['reply_to']) {
                $replyTo = $envelope->getProperties()['reply_to'];
            } else {
                $replyTo = $this->queueConfig->getResponseQueueName($topic);
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
            $channel->basic_publish($msg, $exchange, $topic);
            while ($responseBody === null) {
                try {
                    $channel->wait(null, false, $this->rpcConnectionTimeout);
                } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                    throw new LocalizedException(
                        __(
                            "RPC call failed, connection timed out after %time_out.",
                            ['time_out' => $this->rpcConnectionTimeout]
                        )
                    );
                }
            }
        } else {
            $channel->basic_publish($msg, $exchange, $topic);
        }
        return $responseBody;

    }
}
