<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
     * Publisher queue name.
     *
     * @var string
     */
    private $queue;

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
    private $isDisabled;

    /**
     * Initialize dependencies.
     *
     * @param PublisherConnectionFactory $connectionFactory
     */
    public function __construct(PublisherConnectionFactory $connectionFactory)
    {
        $this->connection = $connectionFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return $this->isDisabled;
    }

    /**
     * @inheritdoc
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @inheritdoc
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Set publisher config item data.
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data)
    {
        $this->topic = $data['topic'];
        $this->queue = $data['queue'] ?? '';
        $this->isDisabled = $data['disabled'];
        $this->connection->setData($data['connection']);
    }
}
