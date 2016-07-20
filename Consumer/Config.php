<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\Iterator;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\IteratorFactory;
use Magento\Framework\Phrase;

/**
 * {@inheritdoc}
 */
class Config implements ConfigInterface
{
    /**
     * @var Iterator
     */
    private $iterator;

    /**
     * Initialize dependencies.
     *
     * @param IteratorFactory $iteratorFactory
     */
    public function __construct(IteratorFactory $iteratorFactory)
    {
        $this->iterator = $iteratorFactory->create();
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
