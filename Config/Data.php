<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Config;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class for access to AMQP configuration.
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Amqp\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\Amqp\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'amqp_config_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }

    /**
     * Identify configured exchange for the provided topic.
     *
     * @param string $topicName
     * @return string
     * @throws LocalizedException
     */
    public function getExchangeForTopic($topicName)
    {
        if (isset($this->_data[Converter::TOPICS][$topicName])) {
            $publisherName = $this->_data[Converter::TOPICS][$topicName][Converter::TOPIC_PUBLISHER];
            if (isset($this->_data[Converter::PUBLISHERS][$publisherName])) {
                return $this->_data[Converter::PUBLISHERS][$publisherName][Converter::PUBLISHER_EXCHANGE];
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

    /**
     * Identify a list of all queue names corresponding to the specified topic (and implicitly exchange).
     *
     * @param string $topic
     * @return string[]
     * @throws LocalizedException
     */
    public function getQueuesForTopic($topic)
    {
        $exchange = $this->getExchangeForTopic($topic);
        /**
         * Exchange should be taken into account here to avoid retrieving queues, related to another exchange,
         * which is not currently associated with topic, but is configured in binds
         */
        $bindKey = $exchange . '--' . $topic;
        if (isset($this->_data[Converter::EXCHANGE_TOPIC_TO_QUEUES_MAP][$bindKey])) {
            return $this->_data[Converter::EXCHANGE_TOPIC_TO_QUEUES_MAP][$bindKey];
        } else {
            throw new LocalizedException(
                new Phrase(
                    'No bindings configured for the "%topic" topic at "%exchange" exchange.',
                    ['topic' => $topic, 'exchange' => $exchange]
                )
            );
        }
    }
}
