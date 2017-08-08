<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * {@inheritdoc}
 * @since 2.2.0
 */
class PublisherConfigItem implements PublisherConfigItemInterface
{
    /**
     * Publisher topic name.
     *
     * @var string
     * @since 2.2.0
     */
    private $topic;

    /**
     * Publisher connection.
     *
     * @var PublisherConnectionInterface
     * @since 2.2.0
     */
    private $connection;

    /**
     * Flag. Is publisher disabled.
     *
     * @var bool
     * @since 2.2.0
     */
    private $isDisabled;

    /**
     * Initialize dependencies.
     *
     * @param PublisherConnectionFactory $connectionFactory
     * @since 2.2.0
     */
    public function __construct(PublisherConnectionFactory $connectionFactory)
    {
        $this->connection = $connectionFactory->create();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function isDisabled()
    {
        return $this->isDisabled;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function setData(array $data)
    {
        $this->topic = $data['topic'];
        $this->isDisabled = $data['disabled'];
        $this->connection->setData($data['connection']);
    }
}
