<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PageCache\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Cache\Manager;
use Magento\PageCache\Model\Cache\Type as PageCacheType;
use Magento\PageCache\Observer\SwitchPageCacheOnMaintenance\PageCacheState;

/**
 * Switch Page Cache on maintenance.
 */
class SwitchPageCacheOnMaintenance implements ObserverInterface
{
    /**
     * @var Manager
     */
    private $cacheManager;

    /**
     * @var PageCacheState
     */
    private $pageCacheStateStorage;

    /**
     * @param Manager $cacheManager
     * @param PageCacheState $pageCacheStateStorage
     */
    public function __construct(Manager $cacheManager, PageCacheState $pageCacheStateStorage)
    {
        $this->cacheManager = $cacheManager;
        $this->pageCacheStateStorage = $pageCacheStateStorage;
    }

    /**
     * Switches Full Page Cache.
     *
     * Depending on enabling or disabling Maintenance Mode it turns off or restores Full Page Cache state.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if ($observer->getData('isOn')) {
            $this->pageCacheStateStorage->save($this->isFullPageCacheEnabled());
            $this->turnOffFullPageCache();
        } else {
            $this->restoreFullPageCacheState();
        }
    }

    /**
     * Turns off Full Page Cache.
     *
     * @return void
     */
    private function turnOffFullPageCache(): void
    {
        if (!$this->isFullPageCacheEnabled()) {
            return;
        }

        $this->cacheManager->clean([PageCacheType::TYPE_IDENTIFIER]);
        $this->cacheManager->setEnabled([PageCacheType::TYPE_IDENTIFIER], false);
    }

    /**
     * Full Page Cache state.
     *
     * @return bool
     */
    private function isFullPageCacheEnabled(): bool
    {
        $cacheStatus = $this->cacheManager->getStatus();

        if (!array_key_exists(PageCacheType::TYPE_IDENTIFIER, $cacheStatus)) {
            return false;
        }

        return (bool)$cacheStatus[PageCacheType::TYPE_IDENTIFIER];
    }

    /**
     * Restores Full Page Cache state.
     *
     * Returns FPC to previous state that was before maintenance mode turning on.
     *
     * @return void
     */
    private function restoreFullPageCacheState(): void
    {
        $storedPageCacheState = $this->pageCacheStateStorage->isEnabled();
        $this->pageCacheStateStorage->flush();

        if ($storedPageCacheState) {
            $this->cacheManager->setEnabled([PageCacheType::TYPE_IDENTIFIER], true);
        }
    }
}
