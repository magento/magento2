<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\HandlerInterface;

/**
 * {@inheritdoc}
 */
class ConsumerConfigItem implements ConsumerConfigItemInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $connection;

    /**
     * @var string
     */
    private $queue;

    /**
     * @var string
     */
    private $consumerInstance;

    /**
     * @var ConsumerConfigItem\HandlerInterface[]
     */
    private $handlers;

    /**
     * @var string
     */
    private $maxMessages;

    /**
     * Initialize data.
     *
     * @param string $name
     * @param string $connection
     * @param string $queue
     * @param string $consumerInstance
     * @param HandlerInterface[] $handlers
     * @param string $maxMessages
     */
    public function __construct(
        $name,
        $connection,
        $queue,
        $consumerInstance,
        $handlers,
        $maxMessages
    ) {
        $this->name = $name;
        $this->connection = $connection;
        $this->queue = $queue;
        $this->consumerInstance = $consumerInstance;
        $this->handlers = $handlers;
        $this->maxMessages = $maxMessages;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
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
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumerInstance()
    {
        return $this->consumerInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxMessages()
    {
        return $this->maxMessages;
    }
}
