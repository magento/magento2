<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query;

use \Magento\Catalog\Api\Data\ProductInterface;

class CustomOptionsPrice
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
     * @var string
     */
    private $connectionName;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\DB\Sql\ColumnValueExpressionFactory $columnValueExpressionFactory,
        $connectionName = 'indexer'
    ) {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
        $this->connectionName = $connectionName;
        $this->columnValueExpressionFactory = $columnValueExpressionFactory;
    }

    public function getSelectForOptionsWithMultipleValues($sourceTable)
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
                ['cw' => $this->getTable('store_website')],
                'cw.website_id = i.website_id',
                []
            )->join(
                ['csg' => $this->getTable('store_group')],
                'csg.group_id = cw.default_group_id',
                []
            )->join(
                ['cs' => $this->getTable('store')],
                'cs.store_id = csg.default_store_id',
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
            )->joinLeft(
                ['otps' => $this->getTable('catalog_product_option_type_price')],
                'otps.option_type_id = otpd.option_type_id AND otpd.store_id = cs.store_id',
                []
            )->group(
                ['i.entity_id', 'i.customer_group_id', 'i.website_id', 'o.option_id']
            );

        $optPriceType = $connection->getCheckSql('otps.option_type_price_id > 0', 'otps.price_type', 'otpd.price_type');
        $optPriceValue = $connection->getCheckSql('otps.option_type_price_id > 0', 'otps.price', 'otpd.price');
        $minPriceRound = $this->columnValueExpressionFactory
            ->create([
                'expression' => "ROUND(i.price * ({$optPriceValue} / 100), 4)"
            ]);
        $minPriceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $minPriceRound);
        $minPriceMin = $this->columnValueExpressionFactory
            ->create([
                'expression' => "MIN({$minPriceExpr})"
            ]);
        $minPrice = $connection->getCheckSql("MIN(o.is_require) = 1", $minPriceMin, '0');

        $tierPriceRound = $this->columnValueExpressionFactory
            ->create([
                'expression' => "ROUND(i.base_tier * ({$optPriceValue} / 100), 4)"
            ]);
        $tierPriceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $tierPriceRound);
        $tierPriceMin = $this->columnValueExpressionFactory
            ->create([
                'expression' => "MIN({$tierPriceExpr})"
            ]);
        $tierPriceValue = $connection->getCheckSql("MIN(o.is_require) > 0", $tierPriceMin, 0);
        $tierPrice = $connection->getCheckSql("MIN(i.base_tier) IS NOT NULL", $tierPriceValue, "NULL");

        $maxPriceRound = $this->columnValueExpressionFactory
            ->create([
                'expression' => "ROUND(i.price * ({$optPriceValue} / 100), 4)"
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

    public function getSelectForOptionsWithOneValue($sourceTable)
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
                ['cw' => $this->getTable('store_website')],
                'cw.website_id = i.website_id',
                []
            )->join(
                ['csg' => $this->getTable('store_group')],
                'csg.group_id = cw.default_group_id',
                []
            )->join(
                ['cs' => $this->getTable('store')],
                'cs.store_id = csg.default_store_id',
                []
            )->join(
                ['o' => $this->getTable('catalog_product_option')],
                'o.product_id = e.' . $metadata->getLinkField(),
                ['option_id']
            )->join(
                ['opd' => $this->getTable('catalog_product_option_price')],
                'opd.option_id = o.option_id AND opd.store_id = 0',
                []
            )->joinLeft(
                ['ops' => $this->getTable('catalog_product_option_price')],
                'ops.option_id = opd.option_id AND ops.store_id = cs.store_id',
                []
            );

        $optPriceType = $connection->getCheckSql('ops.option_price_id > 0', 'ops.price_type', 'opd.price_type');
        $optPriceValue = $connection->getCheckSql('ops.option_price_id > 0', 'ops.price', 'opd.price');

        $minPriceRound = $this->columnValueExpressionFactory
            ->create([
                'expression' => "ROUND(i.price * ({$optPriceValue} / 100), 4)"
            ]);
        $priceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $minPriceRound);
        $minPrice = $connection->getCheckSql("{$priceExpr} > 0 AND o.is_require = 1", $priceExpr, 0);

        $maxPrice = $priceExpr;

        $tierPriceRound = $this->columnValueExpressionFactory
            ->create([
                'expression' => "ROUND(i.base_tier * ({$optPriceValue} / 100), 4)"
            ]);
        $tierPriceExpr = $connection->getCheckSql("{$optPriceType} = 'fixed'", $optPriceValue, $tierPriceRound);
        $tierPriceValue = $connection->getCheckSql("{$tierPriceExpr} > 0 AND o.is_require = 1", $tierPriceExpr, 0);
        $tierPrice = $connection->getCheckSql("i.base_tier IS NOT NULL", $tierPriceValue, "NULL");

        $select->columns(
            [
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'tier_price' => $tierPrice,
            ]
        );

        return $select;
    }

    public function getSelectAggregated($sourceTable)
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

    public function getSelectForUpdate($sourceTable)
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
                'min_price' => new \Zend_Db_Expr('i.min_price + io.min_price'),
                'max_price' => new \Zend_Db_Expr('i.max_price + io.max_price'),
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
    private function getTable($tableName)
    {
        return $this->resource->getTableName($tableName, $this->connectionName);
    }
}
