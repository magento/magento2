<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Amqp\Model;

use Magento\Framework\Amqp\Config\Converter;
use Magento\Framework\Amqp\Config\Data as AmqpConfig;
use Magento\Framework\Amqp\PublisherInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * An AMQP Producer to handle publishing a message.
 */
class AmqpPublisher implements PublisherInterface
{
    /**
     * @var RabbitMqConfig
     */
    private $rabbitMqConfig;

    /**
     * @var AmqpConfig
     */
    private $amqpConfig;

    /**
     * Initialize dependencies.
     *
     * @param RabbitMqConfig $rabbitMqConfig
     * @param AmqpConfig $amqpConfig
     */
    public function __construct(RabbitMqConfig $rabbitMqConfig, AmqpConfig $amqpConfig)
    {
        $this->rabbitMqConfig = $rabbitMqConfig;
        $this->amqpConfig = $amqpConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($topicName, $data)
    {
        $connection = new AMQPStreamConnection(
            $this->rabbitMqConfig->getValue(RabbitMqConfig::HOST),
            $this->rabbitMqConfig->getValue(RabbitMqConfig::PORT),
            $this->rabbitMqConfig->getValue(RabbitMqConfig::USERNAME),
            $this->rabbitMqConfig->getValue(RabbitMqConfig::PASSWORD),
            $this->rabbitMqConfig->getValue(RabbitMqConfig::VIRTUALHOST)
        );
        $channel = $connection->channel();
        $exchange = $this->getExchangeName($topicName);
        $channel->queue_declare($topicName, false, true, false, false);
        $channel->exchange_declare($exchange, 'direct', false, true, false);
        $channel->queue_bind($topicName, $exchange);

        $msg = new AMQPMessage($data, ['content_type' => 'application/json', 'delivery_mode' => 2]);
        $channel->basic_publish($msg, $exchange);

        $channel->close();
        $connection->close();
    }

    /**
     * Identify configured exchange for the provided topic.
     *
     * @param string $topicName
     * @return string
     * @throws LocalizedException
     */
    protected function getExchangeName($topicName)
    {
        $configData = $this->amqpConfig->get();
        if (isset($configData[Converter::TOPICS][$topicName])) {
            $publisherName = $configData[Converter::TOPICS][$topicName][Converter::TOPIC_PUBLISHER];
            if (isset($configData[Converter::PUBLISHERS][$publisherName])) {
                return $configData[Converter::PUBLISHERS][$publisherName][Converter::PUBLISHER_EXCHANGE];
            } else {
                throw new LocalizedException(
                    new Phrase(
                        'Message queue publisher "%publisher" is not configured.',
                        ['publisher' => $publisherName]
                    )
                );
            }
        } else {
            throw new LocalizedException(
                new Phrase('Message queue topic "%topic" is not configured.', ['topic' => $topicName])
            );
        }
    }
}
