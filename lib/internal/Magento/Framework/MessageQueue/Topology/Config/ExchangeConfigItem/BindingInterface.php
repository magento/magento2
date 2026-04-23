<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem;

/**
 * Instances of this interface represent config binging items declared in etc/queue_topology.xsd
 *
 * @api
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
