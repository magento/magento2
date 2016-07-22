<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestModuleSynchronousAmqp\Model;

/**
 * Class SynchronousRpc
 *
 */
class SynchronousRpc implements \Magento\TestModuleSynchronousAmqp\Api\SynchronousRpcInterface
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
     * @param string $simpleDataItem
     * @return string
     */
    public function execute($simpleDataItem)
    {
        return $this->publisher->publish('synchronous.rpc.test', $simpleDataItem);
    }
}
