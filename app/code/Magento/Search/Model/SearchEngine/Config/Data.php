<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\SearchEngine\Config;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data as ConfigData;
use Magento\Framework\Search\SearchEngine\Config\Reader;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides search engine configuration
 * @api
 * @since 100.1.0
 */
class Data extends ConfigData
{
    /**
     * Constructor
     *
     * @param Reader $reader
     * @param CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        Reader $reader,
        CacheInterface $cache,
        $cacheId = 'search_engine_config_cache',
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }
}
