<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\Binding\IteratorFactory;

/**
 * {@inheritdoc}
 * @since 2.2.0
 */
class ExchangeConfigItem implements ExchangeConfigItemInterface
{
    /**
     * Exchange name.
     *
     * @var string
     * @since 2.2.0
     */
    private $name;

    /**
     * Exchange type.
     *
     * @var string
     * @since 2.2.0
     */
    private $type;

    /**
     * Connection name.
     *
     * @var string
     * @since 2.2.0
     */
    private $connection;

    /**
     * Exchange bindings.
     *
     * @var BindingInterface[]
     * @since 2.2.0
     */
    private $bindings;

    /**
     * Exchange arguments.
     *
     * @var array
     * @since 2.2.0
     */
    private $arguments;

    /**
     * Flag. Is exchange durable.
     *
     * @var bool
     * @since 2.2.0
     */
    private $isDurable;

    /**
     * Flag. Is auto-delete
     *
     * @var bool
     * @since 2.2.0
     */
    private $isAutoDelete;

    /**
     * Flag. Is exchange internal.
     *
     * @var bool
     * @since 2.2.0
     */
    private $isInternal;

    /**
     * Initialize dependencies.
     *
     * @param IteratorFactory $iteratorFactory
     * @since 2.2.0
     */
    public function __construct(IteratorFactory $iteratorFactory)
    {
        $this->bindings = $iteratorFactory->create();
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
    public function getType()
    {
        return $this->type;
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
    public function isDurable()
    {
        return $this->isDurable;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function isAutoDelete()
    {
        return $this->isAutoDelete;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function isInternal()
    {
        return $this->isInternal;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Set exchange config item data.
     *
     * @param array $data
     * @return void
     * @since 2.2.0
     */
    public function setData(array $data)
    {
        $this->name = $data['name'];
        $this->type = $data['type'];
        $this->connection = $data['connection'];
        $this->isInternal = $data['internal'];
        $this->isDurable = $data['durable'];
        $this->isAutoDelete = $data['autoDelete'];
        $this->arguments = $data['arguments'];
        $this->bindings->setData($data['bindings']);
    }
}
