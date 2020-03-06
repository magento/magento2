<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Model\Storage;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class DeleteEntitiesFromStores
 *
 * Deletes multiple URL Rewrites from database
 */
class DeleteEntitiesFromStores
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
    }

    /**
     * Function execute
     *
     * Deletes multiple URL Rewrites from database
     *
     * @param array $storeIds
     * @param array $entityIds
     * @param int $entityType
     */
    public function execute($storeIds, $entityIds, $entityType)
    {
        $select = $this->connection->select();
        $select->from($this->resource->getTableName(DbStorage::TABLE_NAME));

        $select->where(
            $this->connection->quoteIdentifier(
                UrlRewrite::STORE_ID
            ) . ' IN (' . $this->connection->quote($storeIds, 'INTEGER') . ')' .
            ' AND ' . $this->connection->quoteIdentifier(
                UrlRewrite::ENTITY_ID
            ) . ' IN (' . $this->connection->quote($entityIds, 'INTEGER') . ')' .
            ' AND ' . $this->connection->quoteIdentifier(
                UrlRewrite::ENTITY_TYPE
            ) . ' = ' . $this->connection->quote($entityType)
        );
        $select = $select->deleteFromSelect($this->resource->getTableName(DbStorage::TABLE_NAME));
        $this->connection->query($select);
    }
}
