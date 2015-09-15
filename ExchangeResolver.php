<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\Amqp\Config\Converter;
use Magento\Framework\Amqp\Config\Data as AmqpConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class ExchangeResolver
{
    /**
     * @var AmqpConfig
     */
    private $amqpConfig;

    public function __construct(AmqpConfig $amqpConfig)
    {
        $this->amqpConfig = $amqpConfig;
    }

    public function resolveExchangeName($topicName)
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
