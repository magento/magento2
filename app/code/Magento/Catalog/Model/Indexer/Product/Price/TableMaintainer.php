<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\Indexer\Table\StrategyInterface;
use Magento\Framework\Model\ResourceModel\Db\Context as DbContext;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\IndexScopeResolverInterface as TableResolver;
use Magento\Indexer\Model\ResourceModel\AbstractResource as AbstractIndexerResource;

/**
 * Class encapsulate logic of work with tables per store in Product Price indexer
 */
class TableMaintainer extends AbstractIndexerResource
{
    /**
     * Catalog product price index table name
     */
    const MAIN_INDEX_TABLE = 'catalog_product_index_price';

    /**
     * @var TableResolver
     */
    private $tableResolver;

    /**
     * Catalog tmp category index table name
     */
    private $tmpTableSuffix = '_temp';

    /**
     * Catalog tmp category index table name
     */
    private $additionalTableSuffix = '_replica';

    /**
     * @var string[]
     */
    private $mainTmpTable;

    /**
     * @param DbContext $context
     * @param StrategyInterface $tableStrategy
     * @param TableResolver $tableResolver
     * @param string|null $connectionName
     */
    public function __construct(
        DbContext $context,
        StrategyInterface $tableStrategy,
        TableResolver $tableResolver,
        $connectionName = null
    ) {
        parent::__construct($context, $tableStrategy, $connectionName);
        $this->tableResolver = $tableResolver;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_INDEX_TABLE, 'entity_id');
    }

    /**
     * Create table based on main table
     *
     * @param string $mainTableName
     * @param string $newTableName
     * @return void
     * @throws \Zend_Db_Exception
     */
    private function createTable(string $mainTableName, string $newTableName)
    {
        if (!$this->getConnection()->isTableExists($newTableName)) {
            $this->getConnection()->createTable(
                $this->getConnection()->createTableByDdl($mainTableName, $newTableName)
            );
        }
    }

    /**
     * Drop table
     *
     * @param string $tableName
     * @return void
     */
    private function dropTable(string $tableName)
    {
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->dropTable($tableName);
        }
    }

    /**
     * Truncate table
     *
     * @param string $tableName
     * @return void
     */
    private function truncateTable(string $tableName)
    {
        if ($this->getConnection()->isTableExists($tableName)) {
            $this->getConnection()->truncateTable($tableName);
        }
    }

    /**
     * Get array key for tmp table
     *
     * @param Dimension[] $dimensions
     * @return string
     */
    private function getArrayKeyForTmpTable(array $dimensions): string
    {
        $key = 'temp';
        foreach ($dimensions as $dimension) {
            $key .= $dimension->getName() . '_' . $dimension->getValue();
        }
        return $key;
    }

    /**
     * Return main index table name using dimensions
     *
     * @param Dimension[] $dimensions
     * @return string
     */
    public function getMainTableByDimensions(array $dimensions): string
    {
        return $this->tableResolver->resolve(self::MAIN_INDEX_TABLE, $dimensions);
    }

    /**
     * Create main and replica index tables for dimensions
     *
     * @param Dimension[] $dimensions
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function createTablesForDimensions(array $dimensions)
    {
        $mainTableName = $this->getMainTableByDimensions($dimensions);
        //Create index table for dimensions based on main replica table
        //Using main replica table is necessary for backward capability and TableResolver plugin work
        $this->createTable(
            $this->getTable(self::MAIN_INDEX_TABLE . $this->additionalTableSuffix),
            $mainTableName
        );

        $mainReplicaTableName = $this->getMainTableByDimensions($dimensions) . $this->additionalTableSuffix;
        //Create replica table for dimensions based on main replica table
        $this->createTable(
            $this->getTable(self::MAIN_INDEX_TABLE . $this->additionalTableSuffix),
            $mainReplicaTableName
        );
    }

    /**
     * Drop main and replica index tables for dimensions
     *
     * @param Dimension[] $dimensions
     * @return void
     */
    public function dropTablesForDimensions(array $dimensions)
    {
        $mainTableName = $this->getMainTableByDimensions($dimensions);
        $this->dropTable($mainTableName);

        $mainReplicaTableName = $this->getMainTableByDimensions($dimensions) . $this->additionalTableSuffix;
        $this->dropTable($mainReplicaTableName);
    }

    /**
     * Truncate main and replica index tables for dimensions
     *
     * @param Dimension[] $dimensions
     * @return void
     */
    public function truncateTablesForDimensions(array $dimensions)
    {
        $mainTableName = $this->getMainTableByDimensions($dimensions);
        $this->truncateTable($mainTableName);

        $mainReplicaTableName = $this->getMainTableByDimensions($dimensions) . $this->additionalTableSuffix;
        $this->truncateTable($mainReplicaTableName);
    }

    /**
     * Return replica index table name
     *
     * @param Dimension[] $dimensions
     * @return string
     */
    public function getMainReplicaTable(array $dimensions): string
    {
        return $this->getMainTableByDimensions($dimensions) . $this->additionalTableSuffix;
    }

    /**
     * Create temporary index table for dimensions
     *
     * @param Dimension[] $dimensions
     * @return void
     */
    public function createMainTmpTable(array $dimensions)
    {
        // Create temporary table based on template table catalog_product_index_price_tmp without indexes
        $templateTableName = $this->_resources->getTableName(self::MAIN_INDEX_TABLE . '_tmp');
        $temporaryTableName = $this->getMainTableByDimensions($dimensions) . $this->tmpTableSuffix;
        $this->getConnection()->createTemporaryTableLike($temporaryTableName, $templateTableName, true);
        $this->mainTmpTable[$this->getArrayKeyForTmpTable($dimensions)] = $temporaryTableName;
    }

    /**
     * Return temporary index table name
     *
     * @param Dimension[] $dimensions
     * @return string
     * @throws \LogicException
     */
    public function getMainTmpTable(array $dimensions): string
    {
        $cacheKey = $this->getArrayKeyForTmpTable($dimensions);
        if (!isset($this->mainTmpTable[$cacheKey])) {
            throw new \LogicException(
                sprintf('Temporary table for provided dimensions "%s" does not exist', $cacheKey)
            );
        }
        return $this->mainTmpTable[$cacheKey];
    }
}
