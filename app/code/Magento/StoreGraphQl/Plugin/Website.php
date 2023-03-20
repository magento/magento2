<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Plugin;

use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity;

/**
 * Website plugin
 */
class Website
{
    /**
     * Add graphql store config tag to the website cache identities.
     *
     * @param \Magento\Store\Model\Website $subject
     * @param array $result
     * @return array
     */
    public function afterGetIdentities(\Magento\Store\Model\Website $subject, array $result): array
    {
        $storeIds = $subject->getStoreIds();
        foreach ($storeIds as $storeId) {
            $result[] = sprintf('%s_%s', ConfigIdentity::CACHE_TAG, $storeId);
        }
        return $result;
    }
}
