<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Topology config data storage. Caches merged config.
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * @param ReaderInterface $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
     */
    public function __construct(
        ReaderInterface $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'message_queue_topology_config_cache',
        ?SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }

    /**
     * Get queues declared explicitly in topology config.
     *
     * @return array
     */
    public function getQueues(): array
    {
        return array_filter(
            $this->get(),
            static fn (array $item): bool => ($item['type'] ?? null) === 'queue'
        );
    }

    /**
     * Get exchanges declared in topology config.
     *
     * @return array
     */
    public function getExchanges(): array
    {
        return array_filter(
            $this->get(),
            static fn (array $item): bool => ($item['type'] ?? null) !== 'queue'
        );
    }
}
