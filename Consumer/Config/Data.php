<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Consumer config data storage. Caches merged config.
 * @since 2.2.0
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * {@inheritdoc}
     * @since 2.2.0
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
