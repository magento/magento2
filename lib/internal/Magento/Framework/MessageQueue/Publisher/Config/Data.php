<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Publisher config data storage. Caches merged config.
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        ReaderInterface $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'message_queue_publisher_config_cache',
        ?SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }
}
