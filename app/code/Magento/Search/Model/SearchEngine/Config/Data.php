<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\SearchEngine\Config;

class Data extends \Magento\Framework\Config\Data
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\Search\SearchEngine\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\Search\SearchEngine\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'search_engine_config_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
