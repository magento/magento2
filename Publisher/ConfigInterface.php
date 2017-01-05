<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * Get publisher configuration by topic.
     *
     * @param string $topic
     * @return PublisherConfigItemInterface
     * @throws LocalizedException
     * @throws \LogicException
     */
    public function getPublisher($topic);

    /**
     * Get list of all publishers declared in the system.
     * 
     * @return PublisherConfigItemInterface[]
     * @throws \LogicException
     */
    public function getPublishers();
}
