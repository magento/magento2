<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Plugin;

use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity;

/**
 * Store plugin to provide identities for cache invalidation
 */
class Store
{
    /**
     * Add graphql store config tag to the store cache identities.
     *
     * @param \Magento\Store\Model\Store $subject
     * @param array $result
     * @return array
     */
    public function afterGetIdentities(\Magento\Store\Model\Store $subject, array $result): array
    {
        $result[] = sprintf('%s_%s', ConfigIdentity::CACHE_TAG, $subject->getId());

        $isActive = $subject->getIsActive();
        // New active store or newly activated store or an active store switched store group
        if ($isActive
            && ($subject->getOrigData('is_active') !== $isActive || $this->isStoreGroupSwitched($subject))
        ) {
            $websiteId = $subject->getWebsiteId();
            if ($websiteId !== null) {
                $result[] = sprintf('%s_%s', ConfigIdentity::CACHE_TAG, 'website_' . $websiteId);
                $storeGroupId = $subject->getStoreGroupId();
                if ($storeGroupId !== null) {
                    $result[] = sprintf(
                        '%s_%s',
                        ConfigIdentity::CACHE_TAG,
                        'website_' . $websiteId . 'group_' . $storeGroupId
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Check whether the store group of the store is switched
     *
     * @param \Magento\Store\Model\Store $store
     * @return bool
     */
    private function isStoreGroupSwitched(\Magento\Store\Model\Store $store): bool
    {
        $origStoreGroupId = $store->getOrigData('group_id');
        $storeGroupId = $store->getStoreGroupId();
        return $origStoreGroupId != null && $origStoreGroupId != $storeGroupId;
    }
}
