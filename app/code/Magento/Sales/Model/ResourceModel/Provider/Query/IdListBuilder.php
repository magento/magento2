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
     * Returns update time of the last row in the grid.
     *
     * @param string $gridTableName
     * @return string
     */
    private function getLastUpdatedAtValue(string $gridTableName): string
    {
        $select = $this->getConnection()->select()
            ->from($this->getConnection()->getTableName($gridTableName), ['updated_at'])
            ->order('updated_at DESC')
            ->limit(1);
        $row = $this->getConnection()->fetchRow($select);

        return $row['updated_at'] ?? '0000-00-00 00:00:00';
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
            ->from($mainTableName, [$mainTableName . '.entity_id']);
        $lastUpdateTime = $this->getLastUpdatedAtValue($gridTableName);
        $select->where($mainTableName . '.updated_at >= ?', $lastUpdateTime);
        foreach ($this->additionalGridTables as $table) {
            $select->joinLeft(
                [$table => $table],
                sprintf(
                    '%s.%s = %s.%s',
                    $mainTableName,
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
