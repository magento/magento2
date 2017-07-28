<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem;

/**
 * Instances of this class represent config binding items declared in etc/queue_topology.xsd
 * @since 2.2.0
 */
class Binding implements BindingInterface
{
    /**
     * Binding id.
     *
     * @var string
     * @since 2.2.0
     */
    private $id;

    /**
     * Binding destination type.
     *
     * @var string
     * @since 2.2.0
     */
    private $destinationType;

    /**
     * Binding destination.
     *
     * @var string
     * @since 2.2.0
     */
    private $destination;

    /**
     * Flag. Is binding disabled.
     * @var bool
     * @since 2.2.0
     */
    private $isDisabled;

    /**
     * Topic name.
     *
     * @var string
     * @since 2.2.0
     */
    private $topic;

    /**
     * Binding arguments.
     *
     * @var array
     * @since 2.2.0
     */
    private $arguments;

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getDestinationType()
    {
        return $this->destinationType;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getDestination()
    {
        return $this->destination;
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
    public function getTopic()
    {
        return $this->topic;
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
     * Set binding data.
     *
     * @param array $data
     * @return void
     * @since 2.2.0
     */
    public function setData(array $data)
    {
        $this->id = $data['id'];
        $this->destinationType = $data['destinationType'];
        $this->destination = $data['destination'];
        $this->arguments = $data['arguments'];
        $this->topic = $data['topic'];
        $this->isDisabled = $data['disabled'];
    }
}
