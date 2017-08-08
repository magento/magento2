<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\Handler\Iterator as HandlerIterator;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\Handler\IteratorFactory as HandlerIteratorFactory;

/**
 * {@inheritdoc}
 * @since 2.2.0
 */
class ConsumerConfigItem implements ConsumerConfigItemInterface
{
    /**
     * @var string
     * @since 2.2.0
     */
    private $name;

    /**
     * @var string
     * @since 2.2.0
     */
    private $connection;

    /**
     * @var string
     * @since 2.2.0
     */
    private $queue;

    /**
     * @var string
     * @since 2.2.0
     */
    private $consumerInstance;

    /**
     * @var HandlerIterator
     * @since 2.2.0
     */
    private $handlers;

    /**
     * @var string
     * @since 2.2.0
     */
    private $maxMessages;

    /**
     * Initialize dependencies.
     *
     * @param HandlerIteratorFactory $handlerIteratorFactory
     * @since 2.2.0
     */
    public function __construct(HandlerIteratorFactory $handlerIteratorFactory)
    {
        $this->handlers = $handlerIteratorFactory->create();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getName()
    {
        return $this->name;
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
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getConsumerInstance()
    {
        return $this->consumerInstance;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getMaxMessages()
    {
        return $this->maxMessages;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setData(array $data)
    {
        $this->name = $data['name'];
        $this->connection = $data['connection'];
        $this->queue = $data['queue'];
        $this->consumerInstance = $data['consumerInstance'];
        $this->maxMessages = $data['maxMessages'];
        $this->handlers->setData($data['handlers']);
    }
}
