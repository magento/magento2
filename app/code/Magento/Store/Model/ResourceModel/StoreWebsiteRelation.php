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
     * @param int|null $storeGroupId
     * @param int|null $storeId
     * @return array
     */
    public function getWebsiteStores(
        int $websiteId,
        bool $available = false,
        int $storeGroupId = null,
        int $storeId = null
    ): array {
        $connection = $this->resource->getConnection();
        $storeTable = $this->resource->getTableName('store_website');
        $storeSelect = $connection->select()
            ->from(['main_table' => $storeTable])
            ->columns(
                [
                    'website_code' => 'code',
                    'website_name' => 'name',
                    'website_sort_order' => 'sort_order',
                    'default_group_id'
                ]
            )
            ->join(
                ['group_table' => $this->resource->getTableName('store_group')],
                'main_table.website_id = group_table.website_id',
                [
                    'store_group_code' => 'code',
                    'store_group_name' => 'name',
                    'default_store_id'
                ]
            )
            ->join(
                ['store_table' => $this->resource->getTableName('store')],
                'group_table.group_id = store_table.group_id'
            );

        if ($storeGroupId) {
            $storeSelect = $storeSelect->where(
                'store_table.group_id = ?',
                $storeGroupId
            );
        }

        if ($storeId) {
            $storeSelect = $storeSelect->where(
                'store_table.store_id = ?',
                $storeId
            );
        }

        if ($available) {
            $storeSelect = $storeSelect->where(
                'store_table.is_active = 1'
            );
        }

        $storeSelect = $storeSelect->where(
            'main_table.website_id = ?',
            $websiteId
        );

        return $connection->fetchAll($storeSelect);
    }
}
