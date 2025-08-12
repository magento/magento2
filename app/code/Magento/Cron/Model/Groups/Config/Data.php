<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\Groups\Config;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides cron groups configuration
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Constructor
     *
     * @param \Magento\Cron\Model\Groups\Config\Reader\Xml $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Cron\Model\Groups\Config\Reader\Xml $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'cron_groups_config_cache',
        ?SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
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
