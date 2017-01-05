<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use \Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItem\Iterator;

/**
 * Publisher config provides access data declared in etc/queue_publisher.xml
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
    public function getPublisher($topic)
    {
        $publisher = $this->iterator[$topic];
        if (!$publisher) {
            throw new LocalizedException(
                new Phrase("Publisher '%publisher' is not declared.", ['publisher' => $topic])
            );
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
