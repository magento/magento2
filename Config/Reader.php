<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config;

use Magento\Framework\MessageQueue\Config\Reader\XmlReader;
use Magento\Framework\MessageQueue\Config\Reader\EnvReader;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;

/**
 * MessageQueue configuration reader.
 */
class Reader implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var XmlReader
     */
    protected $xmlReader;

    /**
     * @var EnvReader
     */
    protected $envReader;

    /**
     * @var array
     */
    protected $envConfig;

    /**
     * @var array
     */
    protected $xmlConfig;

    /**
     * Initialize dependencies.
     *
     * @param XmlReader $xmlConfigReader
     * @param EnvReader $envConfigReader
     */
    public function __construct(
        XmlReader $xmlConfigReader,
        EnvReader $envConfigReader
    ) {
        $this->xmlReader = $xmlConfigReader;
        $this->envReader = $envConfigReader;
    }

    /**
     * Read communication configuration.
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        return $this->merge(
            $this->xmlReader->read($scope),
            $this->envReader->read($scope)
        );
    }

    /**
     * Merge configs
     *
     * @param array $xmlConfig
     * @param array $envConfig
     * @return array
     * @throws LocalizedException
     */
    protected function merge(array $xmlConfig, array $envConfig)
    {
        $config = $this->overrideConsumersData($xmlConfig, $envConfig);
        $config = $this->overridePublishersForTopics($config, $envConfig);
        return $config;
    }

    /**
     * Override consumer connections and max messages declared in queue.xml using values specified in the etc/env.php
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
     * @param array $xmlConfig
     * @param array $envConfig
     * @return array
     * @throws LocalizedException
     */
    protected function overrideConsumersData(array $xmlConfig, array $envConfig)
    {
        $consumers = $xmlConfig[QueueConfig::CONSUMERS] ?: [];
        if (isset($envConfig[EnvReader::ENV_CONSUMERS]) && is_array($envConfig[EnvReader::ENV_CONSUMERS])) {
            foreach ($envConfig[EnvReader::ENV_CONSUMERS] as $consumerName => $consumerConfig) {
                if (isset($consumers[$consumerName])) {
                    if (isset($consumerConfig[EnvReader::ENV_CONSUMER_CONNECTION])) {
                        $consumers[$consumerName][QueueConfig::CONSUMER_CONNECTION]
                            = $consumerConfig[EnvReader::ENV_CONSUMER_CONNECTION];
                    }
                    if (isset($consumerConfig[EnvReader::ENV_CONSUMER_MAX_MESSAGES])) {
                        $consumers[$consumerName][QueueConfig::CONSUMER_MAX_MESSAGES]
                            = $consumerConfig[EnvReader::ENV_CONSUMER_MAX_MESSAGES];
                    }
                }
            }
            $xmlConfig[QueueConfig::CONSUMERS] = $consumers;
        }
        return $xmlConfig;
    }

    /**
     * Override publishers declared for topics in queue.xml using values specified in the etc/env.php
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
     * @param array $xmlConfig
     * @param array $envConfig
     * @return array
     * @throws LocalizedException
     */
    protected function overridePublishersForTopics(array $xmlConfig, array $envConfig)
    {
        $publishers = $xmlConfig[QueueConfig::PUBLISHERS] ?: [];
        $topics = $xmlConfig[QueueConfig::TOPICS] ?: [];
        if (isset($envConfig[EnvReader::ENV_TOPICS]) && is_array($envConfig[EnvReader::ENV_TOPICS])) {
            foreach ($envConfig[EnvReader::ENV_TOPICS] as $topicName => $publisherName) {
                if (!isset($topics[$topicName])) {
                    continue;
                }
                if (isset($publishers[$publisherName])) {
                    $topics[$topicName][QueueConfig::TOPIC_PUBLISHER] = $publisherName;
                } else {
                    throw new LocalizedException(
                        new Phrase(
                            'Publisher "%publisher", specified in env.php for topic "%topic" is not declared.',
                            ['publisher' => $publisherName, 'topic' => $topicName]
                        )
                    );
                }
            }
            $xmlConfig[QueueConfig::TOPICS] = $topics;
        }
        return $xmlConfig;
    }
}
