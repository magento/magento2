<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales configuration data container
 */
namespace Magento\Sales\Model\Config;

use Magento\Framework\Serialize\SerializerInterface;

class Data extends \Magento\Framework\Config\Data
{
    /**
     * Data constructor
     *
     * @param Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Sales\Model\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'sales_totals_config_cache',
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }
}
