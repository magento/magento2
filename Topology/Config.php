<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use \Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\Iterator;

/**
 * Topology config provides access data declared in etc/queue_topology.xml
 */
class Config implements ConfigInterface
{
    /**
     * Exchange config data iterator.
     *
     * @var Iterator
     */
    private $iterator;

    /**
     * Initialize dependencies.
     *
     * @param Iterator $iterator
     */
    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function getExchange($name)
    {
        $topology = $this->iterator[$name];
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
        return $this->iterator;
    }
}
