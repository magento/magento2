<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestModuleAsyncAmqp\Model;

/**
 * Class AsyncPublish
 */
class AsyncPublish implements \Magento\TestModuleAsyncAmqp\Api\AsynchronousInterface
{
    /**
     * @var \Magento\Framework\MessageQueue\PublisherInterface
     */
    protected $publisher;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\MessageQueue\PublisherInterface $publisher
     */
    public function __construct(\Magento\Framework\MessageQueue\PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * @param string $topic
     * @param string $simpleDataItem
     * @return bool
     */
    public function execute($topic, $simpleDataItem)
    {
        try {
            $this->publisher->publish($topic, $simpleDataItem);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
