<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

use \Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;

/**
 * Instances of this class represent config items declared in etc/queue_topology.xsd
 * @api
 */
interface ExchangeConfigItemInterface
{
    /**
     * Get exchange name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get exchange type.
     *
     * @return string
     */
    public function getType();

    /**
     * Get exchange connection.
     *
     * @return string
     */
    public function getConnection();

    /**
     * Check if exchange is durable.
     *
     * @return bool
     */
    public function isDurable();

    /**
     * Check if exchange is auto delete.
     *
     * @return bool
     */
    public function isAutoDelete();

    /**
     * Check if exchange is internal.
     *
     * @return bool
     */
    public function isInternal();

    /**
     * Get exchange bindings.
     *
     * @return BindingInterface[]
     */
    public function getBindings();

    /**
     * Get exchange arguments
     *
     * @return array
     */
    public function getArguments();
}
