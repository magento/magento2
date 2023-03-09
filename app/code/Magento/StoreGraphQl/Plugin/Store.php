<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Plugin;

use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity;

/**
 * Store plugin
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
        if ($subject->isObjectNew()) {
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
}
