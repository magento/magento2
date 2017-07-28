<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

/**
 * Instances of this class represent queue config items.
 * @since 2.2.0
 */
class QueueConfigItem implements QueueConfigItemInterface
{
    /**
     * Queue name.
     *
     * @var string
     * @since 2.2.0
     */
    private $name;

    /**
     * Connection name.
     *
     * @var string
     * @since 2.2.0
     */
    private $connection;

    /**
     * Queue arguments.
     *
     * @var array
     * @since 2.2.0
     */
    private $arguments;

    /**
     * Flag. Is queue durable.
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
    public function getArguments()
    {
        return $this->arguments;
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
     * Set exchange config item data.
     *
     * @param array $data
     * @return void
     * @since 2.2.0
     */
    public function setData(array $data)
    {
        $this->name = $data['name'];
        $this->connection = $data['connection'];
        $this->isDurable = $data['durable'];
        $this->isAutoDelete = $data['autoDelete'];
        $this->arguments = $data['arguments'];
    }
}
