<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItemInterface;

/**
 * Publisher config interface provides access data declared in etc/queue_publisher.xml
 */
interface ConfigInterface
{
    /**
     * Get publisher configuration by publisher name.
     *
     * @param string
     * @return PublisherConfigItemInterface
     * @throws LocalizedException
     */
    public function getPublisher($name);

    /**
     * Get list of all publishers declared in the system.
     * 
     * @return PublisherConfigItemInterface[]
     */
    public function getPublishers();
}
