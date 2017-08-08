<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Instances of this class represent config items declared in etc/queue_publisher.xsd
 * @since 2.2.0
 */
interface PublisherConfigItemInterface
{
    /**
     * Get publisher name.
     *
     * @return string
     * @since 2.2.0
     */
    public function getTopic();

    /**
     * Check if connection disabled.
     *
     * @return bool
     * @since 2.2.0
     */
    public function isDisabled();

    /**
     * Get publisher connection.
     *
     * @return PublisherConnectionInterface
     * @since 2.2.0
     */
    public function getConnection();
}
