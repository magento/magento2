<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;

/**
 * Consumer config interface provides access data declared in etc/queue_consumer.xml
 *
 * @api
 * @since 103.0.0
 */
interface ConfigInterface
{
    /**
     * Get consumer configuration by consumer name.
     *
     * @param string $name
     * @return ConsumerConfigItemInterface
     * @throws LocalizedException
     * @throws \LogicException
     * @since 103.0.0
     */
    public function getConsumer($name);

    /**
     * Get list of all consumers declared in the system.
     *
     * @return ConsumerConfigItemInterface[]
     * @throws \LogicException
     * @since 103.0.0
     */
    public function getConsumers();
}
