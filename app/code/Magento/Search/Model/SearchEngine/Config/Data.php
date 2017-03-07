<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\SearchEngine\Config;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides search engine configuration
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\Search\SearchEngine\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Framework\Search\SearchEngine\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'search_engine_config_cache',
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }
}
