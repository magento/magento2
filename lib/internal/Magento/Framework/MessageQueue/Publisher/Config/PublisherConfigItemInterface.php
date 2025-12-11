<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Instances of this class represent config items declared in etc/queue_publisher.xsd
 * @api
 */
interface PublisherConfigItemInterface
{
    /**
     * Get publisher name.
     *
     * @return string
     */
    public function getTopic();

    /**
     * Get queue name.
     *
     * @return string
     */
    public function getQueue();

    /**
     * Check if connection disabled.
     *
     * @return bool
     */
    public function isDisabled();

    /**
     * Get publisher connection.
     *
     * @return PublisherConnectionInterface
     */
    public function getConnection();
}
