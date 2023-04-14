<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Provider\Query;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Sales\Model\ResourceModel\Grid;

/**
 * Query builder for retrieving list of updated order ids that was not synced to grid table.
 */
class IdListBuilder
{
    /**
     * @var array
     */
    private $additionalGridTables = [];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * IdListBuilder. Builds query for getting updated id list.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Adding additional grid table where entities may already exist.
     *
     * @param string $table
     * @return $this
     */
    public function addAdditionalGridTable(string $table): IdListBuilder
    {
        $this->additionalGridTables[] = $table;

        return $this;
    }

    /**
     * Reset added additional grid table where entities may already exist.
     *
     * @return $this
     */
    public function resetAdditionalGridTable(): IdListBuilder
    {
        $this->additionalGridTables = [];

        return $this;
    }

    /**
     * Returns connection.
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        if (!$this->connection) {
            $this->connection = $this->resourceConnection->getConnection('sales');
        }

        return $this->connection;
    }

    /**
     * Builds select object.
     *
     * @param string $mainTableName
     * @param string $gridTableName
     * @return Select
     */
    public function build(string $mainTableName, string $gridTableName): Select
    {
        $select = $this->getConnection()->select()
            ->from(['main_table' => $mainTableName], ['main_table.entity_id'])
            ->joinLeft(
                ['grid_table' => $this->resourceConnection->getTableName($gridTableName)],
                'main_table.entity_id = grid_table.entity_id',
                []
            );

        $select->where('grid_table.entity_id IS NULL');
        $select->limit(Grid::BATCH_SIZE);
        foreach ($this->additionalGridTables as $table) {
            $select->joinLeft(
                [$table => $table],
                sprintf(
                    '%s.%s = %s.%s',
                    'main_table',
                    'entity_id',
                    $table,
                    'entity_id'
                ),
                []
            )
                ->where($table . '.entity_id IS NULL');
        }
        return $select;
    }
}
