<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\Config;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides cron configuration
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Constructor
     *
     * @param Reader\Xml $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param Reader\Db $dbReader
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Cron\Model\Config\Reader\Xml $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Cron\Model\Config\Reader\Db $dbReader,
        $cacheId = 'crontab_config_cache',
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
        $this->merge($dbReader->get());
    }

    /**
     * Merge cron jobs and return
     *
     * @return array
     */
    public function getJobs()
    {
        return $this->get();
    }
}
