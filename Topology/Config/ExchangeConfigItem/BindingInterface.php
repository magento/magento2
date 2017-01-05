<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem;

/**
 * Instances of this interface represent config binging items declared in etc/queue_topology.xsd
 */
interface BindingInterface
{
    /**
     * Get binding name.
     *
     * @return string
     */
    public function getId();

    /**
     * Get binding destination type.
     *
     * @return string
     */
    public function getDestinationType();

    /**
     * Get destination.
     *
     * @return string
     */
    public function getDestination();

    /**
     * Check if binding is disabled.
     *
     * @return bool
     */
    public function isDisabled();

    /**
     * Get topic name.
     *
     * @return string
     */
    public function getTopic();

    /**
     * Get binding arguments
     *
     * @return array
     */
    public function getArguments();
}
