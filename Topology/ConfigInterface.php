<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItemInterface;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemInterface;

/**
 * Topology config interface provides access data declared in etc/queue_topology.xml
 */
interface ConfigInterface
{
    /**
     * Get exchange configuration by exchange name.
     *
     * @param string $name
     * @param string $connection
     * @return ExchangeConfigItemInterface
     * @throws LocalizedException
     * @throws \LogicException
     */
    public function getExchange($name, $connection);

    /**
     * Get list of all exchanges declared in the system.
     * 
     * @return ExchangeConfigItemInterface[]
     * @throws \LogicException
     */
    public function getExchanges();

    /**
     * Get list of all queues declared in the system.
     *
     * @return QueueConfigItemInterface[]
     * @throws \LogicException
     */
    public function getQueues();
}
