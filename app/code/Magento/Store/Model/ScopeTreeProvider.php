<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeTreeProviderInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;

/**
 * Class \Magento\Store\Model\ScopeTreeProvider
 *
 * @since 2.1.0
 */
class ScopeTreeProvider implements ScopeTreeProviderInterface
{
    /**
     * @var StoreManagerInterface
     * @since 2.1.0
     */
    protected $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @since 2.1.0
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function get()
    {
        $defaultScope = [
            'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            'scope_id' => null,
            'scopes' => [],
        ];

        /** @var Website $website */
        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteScope = [
                'scope' => ScopeInterface::SCOPE_WEBSITES,
                'scope_id' => $website->getId(),
                'scopes' => [],
            ];

            /** @var Group $group */
            foreach ($website->getGroups() as $group) {
                $groupScope = [
                    'scope' => ScopeInterface::SCOPE_GROUP,
                    'scope_id' => $group->getId(),
                    'scopes' => [],
                ];

                /** @var Store $store */
                foreach ($group->getStores() as $store) {
                    $storeScope = [
                        'scope' => ScopeInterface::SCOPE_STORES,
                        'scope_id' => $store->getId(),
                        'scopes' => [],
                    ];
                    $groupScope['scopes'][] = $storeScope;
                }
                $websiteScope['scopes'][] = $groupScope;
            }
            $defaultScope['scopes'][] = $websiteScope;
        }
        return $defaultScope;
    }
}
