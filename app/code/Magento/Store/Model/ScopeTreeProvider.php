<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeTreeProviderInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Class for building scopes tree.
 */
class ScopeTreeProvider implements ScopeTreeProviderInterface
{
    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->groupRepository = $groupRepository;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        $defaultScope = [
            'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            'scope_id' => null,
            'scopes' => [],
        ];

        $groups = [];
        foreach ($this->groupRepository->getList() as $group) {
            $groups[$group->getWebsiteId()][] = $group;
        }
        $stores = [];
        foreach ($this->storeRepository->getList() as $store) {
            $stores[$store->getStoreGroupId()][] = $store;
        }

        /** @var Website $website */
        foreach ($this->websiteRepository->getList() as $website) {
            if (!$website->getId()) {
                continue;
            }

            $websiteScope = [
                'scope' => ScopeInterface::SCOPE_WEBSITES,
                'scope_id' => $website->getId(),
                'scopes' => [],
            ];

            if (!empty($groups[$website->getId()])) {
                /** @var Group $group */
                foreach ($groups[$website->getId()] as $group) {
                    $groupScope = [
                        'scope' => ScopeInterface::SCOPE_GROUP,
                        'scope_id' => $group->getId(),
                        'scopes' => [],
                    ];

                    if (!empty($stores[$group->getId()])) {
                        /** @var Store $store */
                        foreach ($stores[$group->getId()] as $store) {
                            $storeScope = [
                                'scope' => ScopeInterface::SCOPE_STORES,
                                'scope_id' => $store->getId(),
                                'scopes' => [],
                            ];
                            $groupScope['scopes'][] = $storeScope;
                        }
                    }
                    $websiteScope['scopes'][] = $groupScope;
                }
            }

            $defaultScope['scopes'][] = $websiteScope;
        }

        return $defaultScope;
    }
}
