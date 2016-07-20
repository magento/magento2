<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\MessageQueue\Publisher\Config\FlyweightIterator;

/**
 * {@inheritdoc}
 */
class Config implements ConfigInterface
{
    /**
     * Publisher config data iterator.
     *
     * @var FlyweightIterator
     */
    private $iterator;

    /**
     * Initialize dependencies.
     *
     * @param FlyweightIterator $iterator
     */
    public function __construct(FlyweightIterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublisher($name)
    {
        $publisher = $this->iterator[$name];
        if (!$publisher) {
            throw new LocalizedException(new Phrase("Publisher '%publisher' is not declared.", ['publisher' => $name]));
        }
        return $publisher;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublishers()
    {
        return $this->iterator;
    }
}
