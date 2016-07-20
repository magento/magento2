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
     * Publisher topic name.
     *
     * @var string
     */
    private $topic;

    /**
     * Publisher connection.
     *
     * @var PublisherConnectionInterface
     */
    private $connection;

    /**
     * Flag. Is publisher disabled.
     *
     * @var bool
     */
    private $disabled;

    /**
     * Initialize data.
     *
     * @param string $topic
     * @param PublisherConnectionInterface $connection
     * @param bool $disabled
     */
    public function __construct($topic, $connection, $disabled)
    {
        $this->topic = $topic;
        $this->connection = $connection;
        $this->disabled = $disabled;
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
        return $this->disabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
