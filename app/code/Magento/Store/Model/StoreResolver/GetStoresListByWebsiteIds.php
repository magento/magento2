<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreResolver;

use Magento\Store\Api\StoreWebsiteRelationInterface;

/**
 * Retrieves store ids list array by website ids array
 */
class GetStoresListByWebsiteIds
{
    /**
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     */
    public function __construct(
        private readonly StoreWebsiteRelationInterface $storeWebsiteRelation
    ) {
    }

    /**
     * Retrieve list of stores by website ids
     *
     * @param array $websiteIds
     * @return array
     */
    public function execute(array $websiteIds): array
    {
        $storeIdsArray = [];
        foreach ($websiteIds as $websiteId) {
            $storeIdsArray[] = $this->storeWebsiteRelation->getStoreByWebsiteId($websiteId);
        }

        return array_merge([], ...$storeIdsArray);
    }
}
