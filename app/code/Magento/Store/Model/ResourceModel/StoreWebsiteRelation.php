<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Store Website Relation Resource Model
 * @since 2.2.0
 */
class StoreWebsiteRelation
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.2.0
     */
    private $resource;

    /**
     * StoreWebsiteRelation constructor.
     * @param ResourceConnection $resource
     * @since 2.2.0
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param int $websiteId
     * @return array
     * @since 2.2.0
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
}
