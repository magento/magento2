<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Provider;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Model\Grid\LastUpdateTimeCache;

/**
 * Retrieves IDs of entities whose source and grid timestamps differ.
 */
class UpdatedAtListProvider implements NotSyncedDataProviderInterface, NotSyncedDataProviderWithCutoffInterface
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
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->connection = $resourceConnection->getConnection('sales');
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function getIds($mainTableName, $gridTableName)
    {
        $cutoff = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->sub(new \DateInterval('PT1S'))
            ->format('Y-m-d H:i:s');
        return $this->getIdsWithCutoff($mainTableName, $gridTableName, $cutoff);
    }

    /**
     * @inheritdoc
     */
    public function getIdsWithCutoff($mainTableName, $gridTableName, $cutoff)
    {
        $select = $this->connection->select()
            ->from(['main_table' => $this->resourceConnection->getTableName($mainTableName)], ['main_table.entity_id'])
            ->joinInner(
                ['grid_table' => $this->resourceConnection->getTableName($gridTableName)],
                'main_table.entity_id = grid_table.entity_id'
                . ' AND (main_table.updated_at <> grid_table.updated_at OR grid_table.updated_at IS NULL)',
                []
            )->where('main_table.updated_at <= ?', $cutoff);

        return $this->connection->fetchAll($select, [], \Zend_Db::FETCH_COLUMN);
    }
}
