<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\Binding\IteratorFactory;

/**
 * {@inheritdoc}
 */
class ExchangeConfigItem implements ExchangeConfigItemInterface
{
    /**
     * Exchange name.
     *
     * @var string
     */
    private $name;

    /**
     * Exchange type.
     *
     * @var string
     */
    private $type;

    /**
     * Connection name.
     *
     * @var string
     */
    private $connection;

    /**
     * Exchange bindings.
     *
     * @var BindingInterface[]
     */
    private $bindings;

    /**
     * Exchange arguments.
     *
     * @var array
     */
    private $arguments;

    /**
     * Flag. Is exchange durable.
     *
     * @var bool
     */
    private $isDurable;

    /**
     * Flag. Is auto-delete
     *
     * @var bool
     */
    private $isAutoDelete;

    /**
     * Flag. Is exchange internal.
     *
     * @var bool
     */
    private $isInternal;

    /**
     * Initialize dependencies.
     *
     * @param IteratorFactory $iteratorFactory
     */
    public function __construct(IteratorFactory $iteratorFactory)
    {
        $this->bindings = $iteratorFactory->create();
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
    public function getType()
    {
        return $this->type;
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
    public function isDurable()
    {
        return $this->isDurable;
    }

    /**
     * {@inheritdoc}
     */
    public function isAutoDelete()
    {
        return $this->isAutoDelete;
    }

    /**
     * {@inheritdoc}
     */
    public function isInternal()
    {
        return $this->isInternal;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * {@inheritdoc}
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
