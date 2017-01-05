<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Instances of this class represent config items declared in etc/queue_publisher.xsd
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
