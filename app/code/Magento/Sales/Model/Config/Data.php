<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\Config;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides sales configuration
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Constructor
     *
     * @param Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Sales\Model\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'sales_totals_config_cache',
        ?SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }
}
