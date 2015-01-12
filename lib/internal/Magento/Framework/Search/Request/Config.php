<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request;

class Config extends \Magento\Framework\Config\Data
{
    /** Cache ID for Search Request*/
    const CACHE_ID = 'request_declaration';

    /**
     * @param \Magento\Framework\Search\Request\Config\FilesystemReader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\Search\Request\Config\FilesystemReader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = self::CACHE_ID
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
