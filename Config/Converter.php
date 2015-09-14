<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Config;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

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
    const CONSUMER_MAX_MESSAGES = 'max_messages';

    const BINDS = 'binds';
    const BIND_QUEUE = 'queue';
    const BIND_EXCHANGE = 'exchange';
    const BIND_TOPIC = 'topic';

    /**
     * Map which allows optimized search of queues corresponding to the specified exchange and topic pair.
     */
    const EXCHANGE_TOPIC_TO_QUEUES_MAP = 'exchange_topic_to_queues_map';

    const ENV_QUEUE = 'queue';
    const ENV_TOPICS = 'topics';
    const ENV_CONSUMERS = 'consumers';
    const ENV_CONSUMER_CONNECTION = 'connection';
    const ENV_CONSUMER_MAX_MESSAGES = 'max_messages';

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var array
     */
    private $queueConfig;

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
        $this->overrideConsumersData($consumers);
        $binds = $this->extractBinds($source);
        return [
            self::PUBLISHERS => $publishers,
            self::TOPICS => $topics,
            self::CONSUMERS => $consumers,
            self::BINDS => $binds,
            self::EXCHANGE_TOPIC_TO_QUEUES_MAP => $this->buildExchangeTopicToQueuesMap($binds, $topics)
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

    /**
     * Extract consumers configuration.
     *
     * @param \DOMDocument $config
     * @return array
     */
    protected function extractConsumers($config)
    {
        $output = [];
        /** @var $consumerNode \DOMNode */
        foreach ($config->getElementsByTagName('consumer') as $consumerNode) {
            $consumerName = $consumerNode->attributes->getNamedItem('name')->nodeValue;
            $maxMessages = $consumerNode->attributes->getNamedItem('max_messages');
            $output[$consumerName] = [
                self::CONSUMER_NAME => $consumerName,
                self::CONSUMER_QUEUE => $consumerNode->attributes->getNamedItem('queue')->nodeValue,
                self::CONSUMER_CONNECTION => $consumerNode->attributes->getNamedItem('connection')->nodeValue,
                self::CONSUMER_CLASS => $consumerNode->attributes->getNamedItem('class')->nodeValue,
                self::CONSUMER_METHOD => $consumerNode->attributes->getNamedItem('method')->nodeValue,
                self::CONSUMER_MAX_MESSAGES => $maxMessages ? $maxMessages->nodeValue : null,
            ];
        }
        return $output;
    }

    /**
     * Extract binds configuration.
     *
     * @param \DOMDocument $config
     * @return array
     */
    protected function extractBinds($config)
    {
        $output = [];
        /** @var $bindNode \DOMNode */
        foreach ($config->getElementsByTagName('bind') as $bindNode) {
            $output[] = [
                self::BIND_QUEUE => $bindNode->attributes->getNamedItem('queue')->nodeValue,
                self::BIND_EXCHANGE => $bindNode->attributes->getNamedItem('exchange')->nodeValue,
                self::BIND_TOPIC => $bindNode->attributes->getNamedItem('topic')->nodeValue,
            ];
        }
        return $output;
    }

    /**
     * Build map which allows optimized search of queues corresponding to the specified exchange and topic pair.
     *
     * @param array $binds
     * @param array $topics
     * @return array
     */
    protected function buildExchangeTopicToQueuesMap($binds, $topics)
    {
        $output = [];
        $wildcardKeys = [];
        foreach ($binds as $bind) {
            $key = $bind[self::BIND_EXCHANGE] . '--' . $bind[self::BIND_TOPIC];
            if (strpos($key, '*') !== FALSE || strpos($key, '#') !== FALSE) {
                $wildcardKeys[] = $key;
            }
            $output[$key][] = $bind[self::BIND_QUEUE];
        }

        foreach (array_unique($wildcardKeys) as $wildcardKey) {
            $keySplit = explode('--', $wildcardKey);
            $exchangePrefix = $keySplit[0];
            $key = $keySplit[1];
            $pattern = $this->buildWildcardPattern($key);
            foreach (array_keys($topics) as $topic) {
                if (preg_match($pattern, $topic)) {
                    $fullTopic = $exchangePrefix . '--' . $topic;
                    $output[$fullTopic] = array_merge($output[$fullTopic], $output[$wildcardKey]);
                }
            }
            unset($output[$wildcardKey]);
        }
        return $output;
    }

    protected function buildWildcardPattern($wildcardKey)
    {
        $pattern = '/^' . str_replace('.', '\.', $wildcardKey);
        $pattern = str_replace('#', '.+', $pattern);
        $pattern = str_replace('*', '[^\.]+', $pattern);
        if (strpos($wildcardKey, '#') == strlen($wildcardKey)) {
            $pattern .= '/';
        } else {
            $pattern .= '$/';
        }

        return $pattern;
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
        $queueConfig = $this->getQueueConfig();
        if (!isset($queueConfig[self::ENV_TOPICS]) || !is_array($queueConfig[self::ENV_TOPICS])) {
            return;
        }
        foreach ($queueConfig[self::ENV_TOPICS] as $topicName => $publisherName) {
            if (!isset($topics[$topicName])) {
                continue;
            }
            if (isset($publishers[$publisherName])) {
                $topics[$topicName][self::TOPIC_PUBLISHER] = $publisherName;
            } else {
                throw new LocalizedException(
                    new Phrase(
                        'Publisher "%publisher", specified in env.php for topic "%topic" is not declared.',
                        ['publisher' => $publisherName, 'topic' => $topicName]
                    )
                );
            }
        }
    }

    /**
     * Override consumer connections and max messages declared in queue.xml using values specified in the etc/env.php
     *
     * Note that $consumers argument is modified by reference.
     *
     * Example environment config:
     * <code>
     * 'queue' =>
     *     [
     *         'consumers' => [
     *             'customerCreatedListener' => [
     *                  'connection => 'database',
     *                  'max_messages' => '321'
     *              ],
     *         ],
     *     ],
     * </code>
     *
     * @param array &$consumers
     * @return void
     * @throws LocalizedException
     */
    protected function overrideConsumersData(array &$consumers)
    {
        $queueConfig = $this->getQueueConfig();
        if (!isset($queueConfig[self::ENV_CONSUMERS]) || !is_array($queueConfig[self::ENV_CONSUMERS])) {
            return;
        }
        foreach ($queueConfig[self::ENV_CONSUMERS] as $consumerName => $consumerConfig) {
            if (isset($consumers[$consumerName])) {
                if (isset($consumerConfig[self::ENV_CONSUMER_CONNECTION])) {
                    $consumers[$consumerName][self::CONSUMER_CONNECTION]
                        = $consumerConfig[self::ENV_CONSUMER_CONNECTION];
                }
                if (isset($consumerConfig[self::ENV_CONSUMER_MAX_MESSAGES])) {
                    $consumers[$consumerName][self::CONSUMER_MAX_MESSAGES]
                        = $consumerConfig[self::ENV_CONSUMER_MAX_MESSAGES];
                }
            }
        }
    }

    /**
     * Return the queue configuration
     *
     * @return array
     */
    protected function getQueueConfig()
    {
        if ($this->queueConfig == null) {
            $this->queueConfig = $this->deploymentConfig->getConfigData(self::ENV_QUEUE);
        }

        return $this->queueConfig;
    }
}
