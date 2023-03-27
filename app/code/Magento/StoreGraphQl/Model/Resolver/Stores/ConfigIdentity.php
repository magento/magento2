<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver\Stores;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity as StoreConfigIdentity;

class ConfigIdentity implements IdentityInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        foreach ($resolvedData as $storeConfig) {
            $ids[] = sprintf('%s_%s', StoreConfigIdentity::CACHE_TAG, $storeConfig['id']);
        }
        if (!empty($resolvedData)) {
            $websiteId = $resolvedData[0]['website_id'];
            $currentStoreGroupId = $this->getCurrentStoreGroupId($resolvedData);
            $groupTag = $currentStoreGroupId ? 'group_' . $currentStoreGroupId : '';
            $ids[] = sprintf('%s_%s', StoreConfigIdentity::CACHE_TAG, 'website_' . $websiteId . $groupTag);
        }

        return empty($ids) ? [] : array_merge([StoreConfigIdentity::CACHE_TAG], $ids);
    }

    /**
     * Return current store group id if it is certain that useCurrentGroup is true in the query
     *
     * @param array $resolvedData
     * @return string|int|null
     */
    private function getCurrentStoreGroupId(array $resolvedData)
    {
        $storeGroupCodes = array_unique(array_column($resolvedData, 'store_group_code'));
        if (count($storeGroupCodes) == 1) {
            try {
                $store = $this->storeManager->getStore($resolvedData[0]['id']);
                if ($store->getWebsite()->getGroupCollection()->count() != 1) {
                    // There are multiple store groups in the website while there is only one store group
                    // in the resolved data. Therefore useCurrentGroup must be true in the query
                    return $store->getStoreGroupId();
                }
            } catch (NoSuchEntityException $e) {
                // Do nothing
                ;
            }
        }
        return null;
    }
}
