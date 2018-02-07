<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Prepare cron jobs data
 */
namespace Magento\Cron\Model\Groups\Config;

class Data extends \Magento\Framework\Config\Data
{
    /**
     * @param \Magento\Cron\Model\Groups\Config\Reader\Xml $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Cron\Model\Groups\Config\Reader\Xml $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'cron_groups_config_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }

    /**
     * Return config by group id
     *
     * @param string $groupId
     * @return array
     */
    public function getByGroupId($groupId)
    {
        return $this->get()[$groupId];
    }
}
