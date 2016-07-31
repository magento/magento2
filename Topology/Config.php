<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    public function getExchange($name)
    {
        $topology = $this->exchangeIterator[$name];
        if (!$topology) {
            throw new LocalizedException(new Phrase("Exchange '%exchange' is not declared.", ['exchange' => $name]));
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
