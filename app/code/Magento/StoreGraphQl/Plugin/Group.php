<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Plugin;

use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity;

/**
 * Store group plugin to provide identities for cache invalidation
 */
class Group
{
    /**
     * Add graphql store config tag to the store group cache identities.
     *
     * @param \Magento\Store\Model\Group $subject
     * @param array $result
     * @return array
     */
    public function afterGetIdentities(\Magento\Store\Model\Group $subject, array $result): array
    {
        $storeIds = $subject->getStoreIds();
        foreach ($storeIds as $storeId) {
            $result[] = sprintf('%s_%s', ConfigIdentity::CACHE_TAG, $storeId);
        }
        $origWebsiteId = $subject->getOrigData('website_id');
        // An existing store group switches website
        if ($origWebsiteId != null && $origWebsiteId != $subject->getWebsiteId()) {
            $result[] = sprintf('%s_%s', ConfigIdentity::CACHE_TAG, 'website_' . $origWebsiteId);
            $result[] = sprintf(
                '%s_%s',
                ConfigIdentity::CACHE_TAG,
                'website_' . $origWebsiteId . 'group_' . $subject->getId()
            );
        }
        return $result;
    }
}
