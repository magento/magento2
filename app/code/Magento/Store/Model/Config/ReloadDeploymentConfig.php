<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Store\Model\Config;

use Magento\Framework\App\State\ReloadProcessorInterface;
use Magento\Store\App\Config\Type\Scopes;
use Magento\Store\Model\GroupRepository;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\WebsiteRepository;

/**
 * Store module specific reset state part
 */
class ReloadDeploymentConfig implements ReloadProcessorInterface
{

    /**
     * @param StoreRepository $storeRepository
     * @param WebsiteRepository $websiteRepository
     * @param GroupRepository $groupRepository
     * @param Scopes $scopes
     */
    public function __construct(
        private readonly StoreRepository $storeRepository,
        private readonly WebsiteRepository $websiteRepository,
        private readonly GroupRepository $groupRepository,
        private readonly Scopes $scopes
    ) {
    }

    /**
     * Tells the system state to reload itself.
     *
     * @return void
     */
    public function reloadState(): void
    {
        // Note: Magento\Store\Model\StoreManager::reinitStores can't be called because it flushes the caches which
        // we don't want to do because that is already taken care of. Instead, we call the same clean methods that
        // it calls, but we skip cleaning the cache.

        $this->storeRepository->clean();
        $this->websiteRepository->clean();
        $this->groupRepository->clean();

        $this->scopes->clean();
        $this->scopes->get();
    }
}
