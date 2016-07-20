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
     * Initialize dependencies.
     *
     * @param PublisherConnectionFactory $connectionFactory
     */
    public function __construct(PublisherConnectionFactory $connectionFactory)
    {
        $this->connection = $connectionFactory->create();
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

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $this->topic = $data['topic'];
        $this->disabled = $data['disabled'];
        $this->connection->setData($data['connection']);
    }
}
