<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

/**
 * Value class which stores the configuration
 */
class ConsumerConfiguration implements ConsumerConfigurationInterface
{
    const CONSUMER_NAME = "consumer_name";
    const QUEUE_NAME = "queue_name";
    const MAX_MESSAGES = "max_messages";
    const CALLBACK = "callback";

    /**
     * @var array
     */
    private $data;

    /**
     * Initialize dependencies.
     *
     * @param array $data configuration data
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumerName()
    {
        return $this->getData(self::CONSUMER_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxMessages()
    {
        return $this->getData(self::MAX_MESSAGES);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueueName()
    {
        return $this->getData(self::QUEUE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getCallback()
    {
        return $this->getData(self::CALLBACK);
    }

    /**
     * Get specified data item.
     *
     * @param string $key
     * @return string|null
     */
    private function getData($key)
    {
        if (!isset($data[$key])) {
            return null;
        }
        return $data[$key];
    }
}