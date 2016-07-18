<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;


/**
 * Items of this class represent config items declared in etc/queue_publisher.xsd
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
     * Get is connection disabled.
     *
     * @return bool
     */
    public function isDisabled();

    /**
     * Get publisher connections.
     *
     * @return PublisherConnectionInterface[]
     */
    public function getConnections();
}
