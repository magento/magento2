<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

use \Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;

/**
 * Instances of this class represent config items declared in etc/queue_topology.xsd
 * @since 2.2.0
 */
interface ExchangeConfigItemInterface
{
    /**
     * Get exchange name.
     *
     * @return string
     * @since 2.2.0
     */
    public function getName();

    /**
     * Get exchange type.
     *
     * @return string
     * @since 2.2.0
     */
    public function getType();

    /**
     * Get exchange connection.
     *
     * @return string
     * @since 2.2.0
     */
    public function getConnection();

    /**
     * Check if exchange is durable.
     *
     * @return bool
     * @since 2.2.0
     */
    public function isDurable();

    /**
     * Check if exchange is auto delete.
     *
     * @return bool
     * @since 2.2.0
     */
    public function isAutoDelete();

    /**
     * Check if exchange is internal.
     *
     * @return bool
     * @since 2.2.0
     */
    public function isInternal();

    /**
     * Get exchange bindings.
     *
     * @return BindingInterface[]
     * @since 2.2.0
     */
    public function getBindings();

    /**
     * Get exchange arguments
     *
     * @return array
     * @since 2.2.0
     */
    public function getArguments();
}
