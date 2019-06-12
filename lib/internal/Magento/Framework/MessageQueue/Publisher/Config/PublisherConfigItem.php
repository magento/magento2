<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    public function getConnection()
    {
        return $this->connection;
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
        $this->isDisabled = $data['disabled'];
        $this->connection->setData($data['connection']);
    }
}
