<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Config;

use Magento\Framework\Exception\LocalizedException;

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

    const CONSUMERS = 'consumers';
    const CONSUMER_NAME = 'name';
    const CONSUMER_QUEUE = 'queue';
    const CONSUMER_CONNECTION = 'connection';
    const CONSUMER_CLASS = 'class';
    const CONSUMER_METHOD = 'method';

    const ENV_QUEUE = 'queue';
    const ENV_TOPICS = 'topics';

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     */
    public function __construct(\Magento\Framework\App\DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $publishers = $this->extractPublishers($source);
        $topics = $this->extractTopics($source);
        $this->overridePublishersForTopics($topics, $publishers);
        $consumers = $this->extractConsumers($source);
        return [self::PUBLISHERS => $publishers, self::TOPICS => $topics, self::CONSUMERS => $consumers ];
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

    /**
     * Extract consumers configuration.
     *
     * @param \DOMDocument $config
     * @return array
     */
    protected function extractConsumers($config)
    {
        $output = [];
        /** @var $publisherNode \DOMNode */
        foreach ($config->getElementsByTagName('consumer') as $consumerNode) {
            $consumerName = $consumerNode->attributes->getNamedItem('name')->nodeValue;
            $output[$consumerName] = [
                self::CONSUMER_NAME => $consumerName,
                self::CONSUMER_QUEUE => $consumerNode->attributes->getNamedItem('queue')->nodeValue,
                self::CONSUMER_CONNECTION => $consumerNode->attributes->getNamedItem('connection')->nodeValue,
                self::CONSUMER_CLASS => $consumerNode->attributes->getNamedItem('class')->nodeValue,
                self::CONSUMER_METHOD => $consumerNode->attributes->getNamedItem('method')->nodeValue,
            ];
        }
        return $output;
    }

    /**
     * Override publishers declared for topics in queue.xml using values specified in the etc/env.php
     *
     * Note that $topics argument is modified by reference.
     *
     * Example environment config:
     * <code>
     * 'queue' =>
     *     [
     *         'topics' => [
     *             'some_topic_name' => 'custom_publisher',
     *         ],
     *     ],
     * </code>
     *
     * @param array &$topics
     * @param array $publishers
     * @return void
     * @throws LocalizedException
     */
    protected function overridePublishersForTopics(array &$topics, array $publishers)
    {
        $queueConfig =  $this->deploymentConfig->getConfigData(self::ENV_QUEUE);
        if (isset($queueConfig[self::ENV_TOPICS]) && is_array($queueConfig[self::ENV_TOPICS])) {
            foreach ($queueConfig[self::ENV_TOPICS] as $topicName => $publisherName) {
                if (isset($topics[$topicName])) {
                    if (isset($publishers[$publisherName])) {
                        $topics[$topicName][self::TOPIC_PUBLISHER] = $publisherName;
                    } else {
                        throw new LocalizedException(
                            __(
                                'Publisher "%publisher", specified in env.php for topic "%topic" is not declared.',
                                ['publisher' => $publisherName, 'topic' => $topicName]
                            )
                        );
                    }
                }
            }
        }
    }
}
