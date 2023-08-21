<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Store\Model\Config;

use Magento\Framework\App\State\ReloadProcessorInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\App\Config\Type\Scopes;
use Magento\Store\Model\GroupRepository;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\WebsiteRepository;

/**
 * Store module specific reset state part
 */
class ReloadConfig implements ReloadProcessorInterface
{
    /**
     * Tells the system state to reload itself.
     *
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function reloadState(ObjectManagerInterface $objectManager)
    {
        // Note: Magento\Store\Model\StoreManager::reinitStores can't be called because it flushes the caches which
        // we don't want to do because that is already taken care of.  Instead, we call the same clean methods that
        // it calls, but we skip cleaning the cache.

        $objectManager->get(StoreRepository::class)->clean();
        $objectManager->get(WebsiteRepository::class)->clean();
        $objectManager->get(GroupRepository::class)->clean();

        $objectManager->get(Scopes::class)->clean();
        $objectManager->get(Scopes::class)->get();
    }
}
