<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Config;

/**
 * AMQP Helper
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * @param \Magento\Framework\Amqp\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\Amqp\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'constraint_config_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
