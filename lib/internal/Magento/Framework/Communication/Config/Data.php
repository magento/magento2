<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Communication\Config;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides communication configuration
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\Communication\Config\CompositeReader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Framework\Communication\Config\CompositeReader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'communication_config_cache',
        ?SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }
}
