<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Config;

/**
 * Converts AMQP config from \DOMDocument to array
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    const PUBLISHERS = 'publishers';
    const PUBLISHER_NAME = 'name';
    const PUBLISHER_CONNECTION = 'connection';
    const PUBLISHER_EXCHANGE = 'exchange';

    const TOPICS = 'topics';
    const TOPIC_NAME = 'name';
    const TOPIC_PUBLISHER = 'publisher';
    const TOPIC_SCHEMA = 'schema';

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        return [
            self::PUBLISHERS => $this->extractPublishers($source),
            self::TOPICS => $this->extractTopics($source)
        ];
    }

    /**
     * Extract topics configuration.
     *
     * @param \DOMDocument $config
     * @return array
     */
    protected function extractTopics($config)
    {
        $output = [];
        /** @var $topicNode \DOMNode */
        foreach ($config->getElementsByTagName('topic') as $topicNode) {
            $topicName = $topicNode->attributes->getNamedItem('name')->nodeValue;
            $output[$topicName] = [
                self::TOPIC_NAME => $topicName,
                self::TOPIC_SCHEMA => $topicNode->attributes->getNamedItem('schema')->nodeValue,
                self::TOPIC_PUBLISHER => $topicNode->attributes->getNamedItem('publisher')->nodeValue
            ];
        }
        return $output;
    }

    /**
     * Extract publishers configuration.
     *
     * @param \DOMDocument $config
     * @return array
     */
    protected function extractPublishers($config)
    {
        $output = [];
        /** @var $publisherNode \DOMNode */
        foreach ($config->getElementsByTagName('publisher') as $publisherNode) {
            $publisherName = $publisherNode->attributes->getNamedItem('name')->nodeValue;
            $output[$publisherName] = [
                self::PUBLISHER_NAME => $publisherName,
                self::PUBLISHER_CONNECTION => $publisherNode->attributes->getNamedItem('connection')->nodeValue,
                self::PUBLISHER_EXCHANGE => $publisherNode->attributes->getNamedItem('exchange')->nodeValue
            ];
        }
        return $output;
    }
}
