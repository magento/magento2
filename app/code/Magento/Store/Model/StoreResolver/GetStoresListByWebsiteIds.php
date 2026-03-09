<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     */
    public function __construct(StoreWebsiteRelationInterface $storeWebsiteRelation)
    {
        $this->storeWebsiteRelation = $storeWebsiteRelation;
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
