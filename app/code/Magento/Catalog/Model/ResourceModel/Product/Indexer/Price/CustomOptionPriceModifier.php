<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\ColumnValueExpression;

/**
 * Class for modify custom option price.
 */
class CustomOptionPriceModifier implements PriceModifierInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\DB\Sql\ColumnValueExpression
     */
    private $columnValueExpressionFactory;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $dataHelper;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var bool
     */
    private $isPriceGlobalFlag;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Framework\Indexer\Table\StrategyInterface
     */
    private $tableStrategy;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\DB\Sql\ColumnValueExpressionFactory $columnValueExpressionFactory
     * @param \Magento\Catalog\Helper\Data $dataHelper
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\DB\Sql\ColumnValueExpressionFactory $columnValueExpressionFactory,
        \Magento\Catalog\Helper\Data $dataHelper,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        $connectionName = 'indexer'
    ) {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
        $this->connectionName = $connectionName;
        $this->columnValueExpressionFactory = $columnValueExpressionFactory;
        $this->dataHelper = $dataHelper;
        $this->tableStrategy = $tableStrategy;
    }

    /**
     * Apply custom option price to temporary index price table
     *
     * @param IndexTableStructure $priceTable
     * @param array $entityIds
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = [])
    {
        // no need to run all queries if current products have no custom options
        if (!$this->checkIfCustomOptionsExist($priceTable)) {
            return;
        }

        $connection = $this->getConnection();
        $finalPriceTable = $priceTable->getTableName();

        $coaTable = $this->getCustomOptionAggregateTable();
        $this->prepareCustomOptionAggregateTable();

        $copTable = $this->getCustomOptionPriceTable();
        $this->prepareCustomOptionPriceTable();

        $select = $this->getSelectForOptionsWithMultipleValues($finalPriceTable);
        $query = $select->insertFromSelect($coaTable);
        $connection->query($query);

        $select = $this->getSelectForOptionsWithOneValue($finalPriceTable);
        $query = $select->insertFromSelect($coaTable);
        $connection->query($query);

        $select = $this->getSelectAggregated($coaTable);
        $query = $select->insertFromSelect($copTable);
        $connection->query($query);

        // update tmp price index with prices from custom options (from previous aggregated table)
        $select = $this->getSelectForUpdate($copTable);
        $query = $select->crossUpdateFromSelect(['i' => $finalPriceTable]);
        $connection->query($query);

        $connection->delete($coaTable);
        $connection->delete($copTable);
    }

    /**
     * @param IndexTableStructure $priceTable
     * @return bool
     * @throws \Exception
     */
    private function checkIfCustomOptionsExist(IndexTableStructure $priceTable): bool
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);

        $select = $this->getConnection()
            ->select()
            ->from(
                ['i' => $priceTable->getTableName()],
                ['entity_id']
            )->join(
                ['e' => $this->getTable('catalog_product_entity')],
                'e.entity_id = i.entity_id',
                []
            )->join(
                ['o' => $this->getTable('catalog_product_option')],
                'o.product_id = e.' . $metadata->getLinkField(),
                ['option_id']
            );

        return !empty($this->getConnection()->fetchRow($select));
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resource->getConnection($this->connectionName);
        }

        return $this->connection;
    }

    /**
     * Prepare prices for products with custom options that has multiple values
     *
     * @param string $sourceTable
     * @return \Magento\Framework\DB\Select
     * @throws \Exception
     */
    private function getSelectForOptionsWithMultipleValues(string $sourceTable): Select
    {
        $connection = $this->resource->getConnection($this->connectionName);
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);

        $select = $connection->select()
            ->from(
                ['i' => $sourceTable],
                ['entity_id', 'customer_group_id', 'website_id']
            )->join(
                ['e' => $this->getTable('catalog_product_entity')],
                'e.entity_id = i.entity_id',
                []
            )->join(
                ['cwd' => $this->getTable('catalog_product_index_website')],
                'i.website_id = cwd.website_id',
                []
            )->join(
                ['o' => $this->getTable('catalog_product_option')],
                'o.product_id = e.' . $metadata->getLinkField(),
                ['option_id']
            )->join(
                ['ot' => $this->getTable('catalog_product_option_type_value')],
                'ot.option_id = o.option_id',
                []
            )->join(
                ['otpd' => $this->getTable('catalog_product_option_type_price')],
                'otpd.option_type_id = ot.option_type_id AND otpd.store_id = 0',
                []
            )->group(
                ['i.entity_id', 'i.customer_group_id', 'i.website_id', 'o.option_id']
            );

        if ($this->isPriceGlobal()) {
            $optPriceType = 'otpd.price_type';
            $optPriceValue = 'otpd.price';
        } else {
            $select->joinLeft(
                ['otps' => $this->getTable('catalog_product_option_type_price')],
                'otps.option_type_id = otpd.option_type_id AND otpd.store_id = cwd.default_store_id',
                []
            );

            $optPriceType = $connection->getCheckSql(
                'otps.option_type_price_id > 0',
                'otps.price_type',
                'otpd.price_type'
            );
            $optPriceValue = $connection->getCheckSql('otps.option_type_price_id > 0', 'otps.price', 'otpd.price');
        }

        $minPriceRound = $this->columnValueExpressionFactory
            ->create([
                'expression' => "ROUND(i.final_price * ({$optPriceValue} / 100), 4)"
            ]);
        $minPriceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $minPriceRound);
        $minPriceMin = $this->columnValueExpressionFactory
            ->create([
                'expression' => "MIN({$minPriceExpr})"
            ]);
        $minPrice = $connection->getCheckSql("MIN(o.is_require) = 1", $minPriceMin, '0');

        $tierPriceRound = $this->columnValueExpressionFactory
            ->create([
                'expression' => "ROUND(i.tier_price * ({$optPriceValue} / 100), 4)"
            ]);
        $tierPriceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $tierPriceRound);
        $tierPriceMin = $this->columnValueExpressionFactory
            ->create([
                'expression' => "MIN({$tierPriceExpr})"
            ]);
        $tierPriceValue = $connection->getCheckSql("MIN(o.is_require) > 0", $tierPriceMin, 0);
        $tierPrice = $connection->getCheckSql("MIN(i.tier_price) IS NOT NULL", $tierPriceValue, "NULL");

        $maxPriceRound = $this->columnValueExpressionFactory
            ->create([
                'expression' => "ROUND(i.final_price * ({$optPriceValue} / 100), 4)"
            ]);
        $maxPriceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $maxPriceRound);
        $maxPrice = $connection->getCheckSql(
            "(MIN(o.type)='radio' OR MIN(o.type)='drop_down')",
            "MAX({$maxPriceExpr})",
            "SUM({$maxPriceExpr})"
        );

        $select->columns(
            [
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'tier_price' => $tierPrice,
            ]
        );

        return $select;
    }

    /**
     * Prepare prices for products with custom options that has single value
     *
     * @param string $sourceTable
     * @return \Magento\Framework\DB\Select
     * @throws \Exception
     */
    private function getSelectForOptionsWithOneValue(string $sourceTable): Select
    {
        $connection = $this->resource->getConnection($this->connectionName);
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);

        $select = $connection->select()
            ->from(
                ['i' => $sourceTable],
                ['entity_id', 'customer_group_id', 'website_id']
            )->join(
                ['e' => $this->getTable('catalog_product_entity')],
                'e.entity_id = i.entity_id',
                []
            )->join(
                ['cwd' => $this->getTable('catalog_product_index_website')],
                'i.website_id = cwd.website_id',
                []
            )->join(
                ['o' => $this->getTable('catalog_product_option')],
                'o.product_id = e.' . $metadata->getLinkField(),
                ['option_id']
            )->join(
                ['opd' => $this->getTable('catalog_product_option_price')],
                'opd.option_id = o.option_id AND opd.store_id = 0',
                []
            );

        if ($this->isPriceGlobal()) {
            $optPriceType = 'opd.price_type';
            $optPriceValue = 'opd.price';
        } else {
            $select->joinLeft(
                ['ops' => $this->getTable('catalog_product_option_price')],
                'ops.option_id = opd.option_id AND ops.store_id = cwd.default_store_id',
                []
            );

            $optPriceType = $connection->getCheckSql('ops.option_price_id > 0', 'ops.price_type', 'opd.price_type');
            $optPriceValue = $connection->getCheckSql('ops.option_price_id > 0', 'ops.price', 'opd.price');
        }

        $minPriceRound = $this->columnValueExpressionFactory
            ->create([
                'expression' => "ROUND(i.final_price * ({$optPriceValue} / 100), 4)"
            ]);
        $priceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $minPriceRound);
        $minPrice = $connection->getCheckSql("{$priceExpr} > 0 AND o.is_require = 1", $priceExpr, 0);

        $maxPrice = $priceExpr;

        $tierPriceRound = $this->columnValueExpressionFactory
            ->create([
                'expression' => "ROUND(i.tier_price * ({$optPriceValue} / 100), 4)"
            ]);
        $tierPriceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $tierPriceRound);
        $tierPriceValue = $connection->getCheckSql("{$tierPriceExpr} > 0 AND o.is_require = 1", $tierPriceExpr, 0);
        $tierPrice = $connection->getCheckSql("i.tier_price IS NOT NULL", $tierPriceValue, "NULL");

        $select->columns(
            [
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'tier_price' => $tierPrice,
            ]
        );

        return $select;
    }

    /**
     * Aggregate prices with one and multiply options into one table
     *
     * @param string $sourceTable
     * @return \Magento\Framework\DB\Select
     */
    private function getSelectAggregated(string $sourceTable): Select
    {
        $connection = $this->resource->getConnection($this->connectionName);

        $select = $connection->select()
            ->from(
                [$sourceTable],
                [
                    'entity_id',
                    'customer_group_id',
                    'website_id',
                    'min_price' => 'SUM(min_price)',
                    'max_price' => 'SUM(max_price)',
                    'tier_price' => 'SUM(tier_price)',
                ]
            )->group(
                ['entity_id', 'customer_group_id', 'website_id']
            );

        return $select;
    }

    /**
     * @param string $sourceTable
     * @return \Magento\Framework\DB\Select
     */
    private function getSelectForUpdate(string $sourceTable): Select
    {
        $connection = $this->resource->getConnection($this->connectionName);

        $select = $connection->select()->join(
            ['io' => $sourceTable],
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id',
            []
        );
        $select->columns(
            [
                'min_price' => new ColumnValueExpression('i.min_price + io.min_price'),
                'max_price' => new ColumnValueExpression('i.max_price + io.max_price'),
                'tier_price' => $connection->getCheckSql(
                    'i.tier_price IS NOT NULL',
                    'i.tier_price + io.tier_price',
                    'NULL'
                ),
            ]
        );

        return $select;
    }

    /**
     * @param string $tableName
     * @return string
     */
    private function getTable(string $tableName): string
    {
        return $this->resource->getTableName($tableName, $this->connectionName);
    }

    /**
     * @return bool
     */
    private function isPriceGlobal(): bool
    {
        if ($this->isPriceGlobalFlag === null) {
            $this->isPriceGlobalFlag = $this->dataHelper->isPriceGlobal();
        }

        return $this->isPriceGlobalFlag;
    }

    /**
     * Retrieve table name for custom option temporary aggregation data
     *
     * @return string
     */
    private function getCustomOptionAggregateTable(): string
    {
        return $this->tableStrategy->getTableName('catalog_product_index_price_opt_agr');
    }

    /**
     * Retrieve table name for custom option prices data
     *
     * @return string
     */
    private function getCustomOptionPriceTable(): string
    {
        return $this->tableStrategy->getTableName('catalog_product_index_price_opt');
    }

    /**
     * Prepare table structure for custom option temporary aggregation data
     *
     * @return void
     */
    private function prepareCustomOptionAggregateTable()
    {
        $this->getConnection()->delete($this->getCustomOptionAggregateTable());
    }

    /**
     * Prepare table structure for custom option prices data
     *
     * @return void
     */
    private function prepareCustomOptionPriceTable()
    {
        $this->getConnection()->delete($this->getCustomOptionPriceTable());
    }
}
