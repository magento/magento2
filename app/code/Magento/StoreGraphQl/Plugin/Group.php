<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Plugin;

use Magento\Store\Model\Group as ModelGroup;
use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity;

/**
 * Store group plugin
 */
class Group
{
    /**
     * Add graphql store config tag to the store group cache identities.
     *
     * @param ModelGroup $subject
     * @param array $result
     * @return array
     */
    public function afterGetIdentities(ModelGroup $subject, array $result): array
    {
        $storeIds = $subject->getStoreIds();
        foreach ($storeIds as $storeId) {
            $result[] = sprintf('%s_%s', ConfigIdentity::CACHE_TAG, $storeId);
        }
        return $result;
    }
}
