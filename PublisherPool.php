<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

/**
 * Publishers pool.
 */
class PublisherPool
{
    /**
     * Publishers list.
     *
     * @var \Magento\Framework\MessageQueue\PublisherInterface[]
     */
    protected $publishers;

    /**
     * Communication config.
     *
     * @var \Magento\Framework\Communication\Config
     */
    protected $config;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Communication\Config $config
     * @param PublisherInterface[] $publishers
     */
    public function __construct(\Magento\Framework\Communication\Config $config, array $publishers)
    {
        $this->publishers = $publishers;
        $this->config = $config;
    }

    /**
     * Get publisher.
     *
     * @param string $type - Possible values sync|async
     * @return PublisherInterface
     */
    public function get($type)
    {
        if (!isset($this->publishers[$type])) {
            throw new \InvalidArgumentException('Unknown publisher type ' . $type);
        }
        return $this->publishers[$type];
    }

    /**
     * Get publisher by topic.
     *
     * @param string $topicName
     * @return PublisherInterface
     */
    public function getByTopicType($topicName)
    {
        $topic = $this->config->getTopic($topicName);
        $type = $topic[\Magento\Framework\Communication\ConfigInterface::TOPIC_IS_SYNCHRONOUS] ? 'sync' : 'async';
        return $this->get($type);
    }
}