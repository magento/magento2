<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Store Website Relation Resource Model
 */
class StoreWebsiteRelation
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * StoreWebsiteRelation constructor.
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get store by website id
     *
     * @param int $websiteId
     * @return array
     */
    public function getStoreByWebsiteId($websiteId)
    {
        $connection = $this->resource->getConnection();
        $storeTable = $this->resource->getTableName('store');
        $storeSelect = $connection->select()->from($storeTable, ['store_id'])->where(
            'website_id = ?',
            $websiteId
        );
        $data = $connection->fetchCol($storeSelect);
        return $data;
    }

    /**
     * Get website store data
     *
     * @param int $websiteId
     * @param bool $available
     * @return array
     */
    public function getWebsiteStores(int $websiteId, bool $available = false): array
    {
        $connection = $this->resource->getConnection();
        $storeTable = $this->resource->getTableName('store');
        $storeSelect = $connection->select()->from($storeTable)->where(
            'website_id = ?',
            $websiteId
        );

        if ($available) {
            $storeSelect = $storeSelect->where(
                'is_active = 1'
            );
        }

        return $connection->fetchAll($storeSelect);
    }
}
