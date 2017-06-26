<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Class StockStatusFilter
 * Adds filter by stock status to base select
 */
class StockStatusFilter
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Defines strategies of how filter should be applied
     */
    const FILTER_JUST_ENTITY = 'general_filter';
    const FILTER_ENTITY_AND_SUB_PRODUCTS = 'filter_with_sub_products';

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ConditionManager $conditionManager
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ConditionManager $conditionManager,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->conditionManager = $conditionManager;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Adds filter by stock status to base select
     *
     * @param Select $select
     * @param mixed $stockValues
     * @param string $type
     * @param bool $showOutOfStockFlag
     * @return Select
     * @throws \InvalidArgumentException
     */
    public function apply(Select $select, $stockValues, $type, $showOutOfStockFlag)
    {
        if ($type !== self::FILTER_JUST_ENTITY && $type !== self::FILTER_ENTITY_AND_SUB_PRODUCTS) {
            throw new \InvalidArgumentException(sprintf('Invalid filter type: %s', $type));
        }

        $select = clone $select;
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $this->addMainStockStatusJoin($select, $stockValues, $mainTableAlias, $showOutOfStockFlag);

        if ($type === self::FILTER_ENTITY_AND_SUB_PRODUCTS) {
            $this->addSubProductsStockStatusJoin($select, $stockValues, $mainTableAlias, $showOutOfStockFlag);
        }

        return $select;
    }

    /**
     * Adds filter join for products by stock status
     * In case when $showOutOfStockFlag is true - joins are still required to filter only enabled products
     *
     * @param Select $select
     * @param array|int $stockValues
     * @param string $mainTableAlias
     * @param bool $showOutOfStockFlag
     * @return void
     */
    private function addMainStockStatusJoin(Select $select, $stockValues, $mainTableAlias, $showOutOfStockFlag)
    {
        $catalogInventoryTable = $this->resourceConnection->getTableName('cataloginventory_stock_status');
        $select->joinInner(
            ['stock_index' => $catalogInventoryTable],
            $this->conditionManager->combineQueries(
                [
                    sprintf('stock_index.product_id = %s.entity_id', $mainTableAlias),
                    $this->conditionManager->generateCondition(
                        'stock_index.website_id',
                        '=',
                        $this->stockConfiguration->getDefaultScopeId()
                    ),
                    $showOutOfStockFlag
                        ? ''
                        : $this->conditionManager->generateCondition(
                            'stock_index.stock_status',
                            is_array($stockValues) ? 'in' : '=',
                            $stockValues
                        ),
                    $this->conditionManager->generateCondition(
                        'stock_index.stock_id',
                        '=',
                        (int) $this->stockRegistry->getStock()->getStockId()
                    ),
                ],
                Select::SQL_AND
            ),
            []
        );
    }

    /**
     * Adds filter join for sub products by stock status
     * In case when $showOutOfStockFlag is true - joins are still required to filter only enabled products
     *
     * @param Select $select
     * @param array|int $stockValues
     * @param string $mainTableAlias
     * @param bool $showOutOfStockFlag
     * @return void
     */
    private function addSubProductsStockStatusJoin(Select $select, $stockValues, $mainTableAlias, $showOutOfStockFlag)
    {
        $catalogInventoryTable = $this->resourceConnection->getTableName('cataloginventory_stock_status');
        $select->joinInner(
            ['sub_products_stock_index' => $catalogInventoryTable],
            $this->conditionManager->combineQueries(
                [
                    sprintf('sub_products_stock_index.product_id = %s.source_id', $mainTableAlias),
                    $this->conditionManager->generateCondition(
                        'sub_products_stock_index.website_id',
                        '=',
                        $this->stockConfiguration->getDefaultScopeId()
                    ),
                    $showOutOfStockFlag
                        ? ''
                        : $this->conditionManager->generateCondition(
                            'sub_products_stock_index.stock_status',
                            is_array($stockValues) ? 'in' : '=',
                            $stockValues
                        ),
                    $this->conditionManager->generateCondition(
                        'sub_products_stock_index.stock_id',
                        '=',
                        (int) $this->stockRegistry->getStock()->getStockId()
                    ),
                ],
                Select::SQL_AND
            ),
            []
        );
    }

    /**
     * Extracts alias for table that is used in FROM clause in Select
     *
     * @param Select $select
     * @return string|null
     */
    private function extractTableAliasFromSelect(Select $select)
    {
        $fromArr = array_filter(
            $select->getPart(Select::FROM),
            function ($fromPart) {
                return $fromPart['joinType'] === Select::FROM;
            }
        );

        return $fromArr ? array_keys($fromArr)[0] : null;
    }
}
