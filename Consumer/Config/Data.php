<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Consumer config data storage. Caches merged config.
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Framework\MessageQueue\Consumer\Config\ReaderInterface $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'message_queue_consumer_config_cache',
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }
}
