<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Model\Grid\LastUpdateTimeCache;

/**
 * Retrieves ID's of not synced by `updated_at` column entities.
 * The result should contain list of entities ID's from the main table which have `updated_at` column greater
 * than in the grid table.
 */
class UpdatedAtListProvider implements NotSyncedDataProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var LastUpdateTimeCache
     */
    private $lastUpdateTimeCache;

    /**
     * @param ResourceConnection $resourceConnection
     * @param LastUpdateTimeCache $lastUpdateTimeCache
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LastUpdateTimeCache $lastUpdateTimeCache
    ) {
        $this->connection = $resourceConnection->getConnection('sales');
        $this->resourceConnection = $resourceConnection;
        $this->lastUpdateTimeCache = $lastUpdateTimeCache;
    }

    /**
     * @inheritdoc
     */
    public function getIds($mainTableName, $gridTableName)
    {
        $mainTableName = $this->resourceConnection->getTableName($mainTableName);
        $gridTableName = $this->resourceConnection->getTableName($gridTableName);
        $select = $this->connection->select()
            ->from($mainTableName, [$mainTableName . '.entity_id'])
            ->joinInner(
                [$gridTableName => $gridTableName],
                sprintf(
                    '%s.entity_id = %s.entity_id AND %s.updated_at > %s.updated_at',
                    $mainTableName,
                    $gridTableName,
                    $mainTableName,
                    $gridTableName
                ),
                []
            );

        $lastUpdatedAt = $this->lastUpdateTimeCache->get($gridTableName);
        if ($lastUpdatedAt) {
            $select->where($mainTableName . '.updated_at > ?', $lastUpdatedAt);
        }

        return $this->connection->fetchAll($select, [], \Zend_Db::FETCH_COLUMN);
    }
}
