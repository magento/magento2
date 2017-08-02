<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Plugin\Model\Product\Action;

use Magento\Catalog\Model\Product\Action;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class \Magento\Catalog\Plugin\Model\Product\Action\UpdateAttributesFlushCache
 *
 * @since 2.0.0
 */
class UpdateAttributesFlushCache
{
    /**
     * @var CacheContext
     * @since 2.0.0
     */
    protected $cacheContext;

    /**
     * @var EventManager
     * @since 2.0.0
     */
    protected $eventManager;

    /**
     * @param CacheContext $cacheContext
     * @param EventManager $eventManager
     * @since 2.0.0
     */
    public function __construct(
        CacheContext $cacheContext,
        EventManager $eventManager
    ) {
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
    }

    /**
     * @param Action $subject
     * @param Action $result
     * @return Action
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function afterUpdateAttributes(
        \Magento\Catalog\Model\Product\Action $subject,
        \Magento\Catalog\Model\Product\Action $result
    ) {
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        return $result;
    }

    /**
     * @param Action $subject
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function afterUpdateWebsites(
        \Magento\Catalog\Model\Product\Action $subject
    ) {
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }
}
