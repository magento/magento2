<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Topology;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\Iterator as ExchangeIterator;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItem\Iterator as QueueIterator;

/**
 * Topology config provides access to data declared in etc/queue_topology.xml
 */
class Config implements ConfigInterface
{
    /**
     * Exchange config data iterator.
     *
     * @var ExchangeIterator
     */
    private $exchangeIterator;

    /**
     * Exchange config data iterator.
     *
     * @var ExchangeIterator
     */
    private $queueIterator;

    /**
     * Initialize dependencies.
     *
     * @param ExchangeIterator $exchangeIterator
     * @param QueueIterator $queueIterator
     */
    public function __construct(ExchangeIterator $exchangeIterator, QueueIterator $queueIterator)
    {
        $this->exchangeIterator = $exchangeIterator;
        $this->queueIterator = $queueIterator;
    }

    /**
     * {@inheritdoc}
     */
    public function getExchange($name, $connection)
    {
        $topology = $this->exchangeIterator[$name . '--' . $connection];
        if (!$topology) {
            throw new LocalizedException(
                new Phrase(
                    'The "%exchange" exchange is not declared for the "%connection" connection. Verify and try again.',
                    [
                        'exchange' => $name,
                        'connection' => $connection
                    ]
                )
            );
        }
        return $topology;
    }

    /**
     * {@inheritdoc}
     */
    public function getExchanges()
    {
        return $this->exchangeIterator;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueues()
    {
        return $this->queueIterator;
    }
}
