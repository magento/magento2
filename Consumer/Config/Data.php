<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

use Magento\Framework\MessageQueue\Consumer\Config\DataInterface;

/**
 * Consumer config data storage. Caches merged config.
 */
class Data extends \Magento\Framework\Config\Data implements DataInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Framework\MessageQueue\Consumer\Config\ReaderInterface $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'message_queue_consumer_config_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
