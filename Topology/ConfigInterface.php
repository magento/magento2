<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItemInterface;

/**
 * Topology config interface provides access data declared in etc/queue_topology.xml
 */
interface ConfigInterface
{
    /**
     * Get exchange configuration by exchange name.
     *
     * @param string $name
     * @return ExchangeConfigItemInterface
     * @throws LocalizedException
     */
    public function getExchange($name);

    /**
     * Get list of all exchanges declared in the system.
     * 
     * @return ExchangeConfigItemInterface[]
     */
    public function getExchanges();
}
