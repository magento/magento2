<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\PageCache\Model\Cache;

use Magento\Framework\Cache\CacheConstants;

/**
 * System / Cache Management / Cache type "Full Page Cache"
 *
 */
class Type extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    /**
     * Cache type code unique among all cache types
     */
    public const TYPE_IDENTIFIER = 'full_page';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    public const CACHE_TAG = 'FPC';

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
        $this->eventManager = $eventManager;
    }

    /**
     * @inheritDoc
     *
     * @param string $mode
     * @param array $tags
     * @return bool
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, array $tags = [])
    {
        $this->eventManager->dispatch('adminhtml_cache_refresh_type');
        return parent::clean($mode, $tags);
    }
}
