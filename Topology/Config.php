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
 * @since 2.2.0
 */
class Config implements ConfigInterface
{
    /**
     * Exchange config data iterator.
     *
     * @var ExchangeIterator
     * @since 2.2.0
     */
    private $exchangeIterator;

    /**
     * Exchange config data iterator.
     *
     * @var ExchangeIterator
     * @since 2.2.0
     */
    private $queueIterator;

    /**
     * Initialize dependencies.
     *
     * @param ExchangeIterator $exchangeIterator
     * @param QueueIterator $queueIterator
     * @since 2.2.0
     */
    public function __construct(ExchangeIterator $exchangeIterator, QueueIterator $queueIterator)
    {
        $this->exchangeIterator = $exchangeIterator;
        $this->queueIterator = $queueIterator;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getExchange($name, $connection)
    {
        $topology = $this->exchangeIterator[$name . '--' . $connection];
        if (!$topology) {
            throw new LocalizedException(
                new Phrase(
                    "Exchange '%exchange' is not declared for connection '%connection'.",
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
     * @since 2.2.0
     */
    public function getExchanges()
    {
        return $this->exchangeIterator;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getQueues()
    {
        return $this->queueIterator;
    }
}
