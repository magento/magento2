<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Config;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data as ConfigData;
use Magento\Framework\Config\ReaderInterface;

/**
 * Represents loaded and cached Analytics configuration data.
 */
class Data extends ConfigData
{
    /**
     * @param ReaderInterface $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        ReaderInterface $reader,
        CacheInterface $cache,
        $cacheId = 'magento_analytics_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
