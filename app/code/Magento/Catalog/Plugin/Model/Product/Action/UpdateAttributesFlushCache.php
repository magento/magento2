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
 */
class UpdateAttributesFlushCache
{
    /**
     * @var CacheContext
     */
    protected $cacheContext;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @param CacheContext $cacheContext
     * @param EventManager $eventManager
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
     */
    public function afterUpdateWebsites(
        \Magento\Catalog\Model\Product\Action $subject
    ) {
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }
}
