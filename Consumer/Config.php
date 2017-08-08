<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\Iterator;
use Magento\Framework\Phrase;

/**
 * {@inheritdoc}
 * @since 2.2.0
 */
class Config implements ConfigInterface
{
    /**
     * Item iterator.
     *
     * @var Iterator
     * @since 2.2.0
     */
    private $iterator;

    /**
     * Initialize dependencies.
     *
     * @param Iterator $iterator
     * @since 2.2.0
     */
    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getConsumers()
    {
        return $this->iterator;
    }
}
