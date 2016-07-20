<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use \Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItem\Iterator;

/**
 * {@inheritdoc}
 */
class Config implements ConfigInterface
{
    /**
     * Publisher config data iterator.
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
