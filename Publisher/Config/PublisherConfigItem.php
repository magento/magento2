<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * {@inheritdoc}
 */
class PublisherConfigItem implements PublisherConfigItemInterface
{
    /**
     * @var string
     */
    private $topic;

    /**
     * @var PublisherConnectionInterface[]
     */
    private $connections;

    /**
     * @var bool
     */
    private $isDisabled;

    /**
     * Initialize data.
     *
     * @param string $topic
     * @param PublisherConnectionInterface[] $connections
     * @param bool $isDisabled
     */
    public function __construct($topic, $connections, $isDisabled)
    {
        $this->topic = $topic;
        $this->connections = $connections;
    }

    /**
     * {@inheritdoc}
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * {@inheritdoc}
     */
    public function isDisabled()
    {
        return $this->isDisabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnections()
    {
        return $this->connections;
    }
}
