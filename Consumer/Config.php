<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\Iterator;
use Magento\Framework\Phrase;

/**
 * {@inheritdoc}
 */
class Config implements ConfigInterface
{
    /**
     * Item iterator.
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
    public function getConsumer($name)
    {
        $consumer = $this->iterator[$name];
        if (!$consumer) {
            throw new LocalizedException(new Phrase("Consumer '%consumer' is not declared.", ['consumer' => $name]));
        }
        return $consumer;
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumers()
    {
        return $this->iterator;
    }
}
