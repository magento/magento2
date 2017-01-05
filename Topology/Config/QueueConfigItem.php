<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

/**
 * Instances of this class represent queue config items.
 */
class QueueConfigItem implements QueueConfigItemInterface
{
    /**
     * Queue name.
     *
     * @var string
     */
    private $name;

    /**
     * Connection name.
     *
     * @var string
     */
    private $connection;

    /**
     * Queue arguments.
     *
     * @var array
     */
    private $arguments;

    /**
     * Flag. Is queue durable.
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
    public function getArguments()
    {
        return $this->arguments;
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
     * Set exchange config item data.
     *
     * @param array $data
     * @return void
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
