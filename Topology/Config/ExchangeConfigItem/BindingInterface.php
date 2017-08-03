<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem;

/**
 * Instances of this interface represent config binging items declared in etc/queue_topology.xsd
 * @since 2.2.0
 */
interface BindingInterface
{
    /**
     * Get binding name.
     *
     * @return string
     * @since 2.2.0
     */
    public function getId();

    /**
     * Get binding destination type.
     *
     * @return string
     * @since 2.2.0
     */
    public function getDestinationType();

    /**
     * Get destination.
     *
     * @return string
     * @since 2.2.0
     */
    public function getDestination();

    /**
     * Check if binding is disabled.
     *
     * @return bool
     * @since 2.2.0
     */
    public function isDisabled();

    /**
     * Get topic name.
     *
     * @return string
     * @since 2.2.0
     */
    public function getTopic();

    /**
     * Get binding arguments
     *
     * @return array
     * @since 2.2.0
     */
    public function getArguments();
}
