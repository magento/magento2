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
        $origStoreGroupId = $subject->getOrigData('group_id');
        $origIsActive = $subject->getOrigData('is_active');
        // An existing active store switches store group
        if ($origIsActive && $origStoreGroupId != null && $origStoreGroupId != $subject->getStoreGroupId()) {
            $origWebsiteId = $subject->getOrigData('website_id');
            $result[] = sprintf('%s_%s', ConfigIdentity::CACHE_TAG, 'website_' . $origWebsiteId);
            $result[] = sprintf(
                '%s_%s',
                ConfigIdentity::CACHE_TAG,
                'website_' . $origWebsiteId . 'group_' . $origStoreGroupId
            );
        }
        // New active store or newly activated store or an active store switched store group
        $storeGroupId = $subject->getStoreGroupId();
        $isActive = $subject->getIsActive();
        if ($isActive && (
            $subject->getOrigData('is_active') !== $isActive
            || ($origStoreGroupId != null && $origStoreGroupId != $storeGroupId)
            )
        ) {
            $websiteId = $subject->getWebsiteId();
            if ($websiteId !== null) {
                $result[] = sprintf('%s_%s', ConfigIdentity::CACHE_TAG, 'website_' . $websiteId);
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
}
