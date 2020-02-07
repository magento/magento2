<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Adds filter by stock status to base select
 *
 * @deprecated 101.0.0 MySQL search engine is not recommended.
 * @see \Magento\ElasticSearch
 */
class StockStatusFilter
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Defines strategy of how filter should be applied
     *
     * Stock status filter will be applied only on parent products
     * (e.g. only for configurable products, without options)
     */
    const FILTER_JUST_ENTITY = 'general_filter';

    /**
     * Defines strategy of how filter should be applied
     *
     * Stock status filter will be applied on parent products with its child
     * (e.g. for configurable products and options)
     */
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
     * @var StockStatusQueryBuilder
     */
    private $stockStatusQueryBuilder;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ConditionManager $conditionManager
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockStatusQueryBuilder|null $stockStatusQueryBuilder
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ConditionManager $conditionManager,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        ?StockStatusQueryBuilder $stockStatusQueryBuilder = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->conditionManager = $conditionManager;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
        $this->stockStatusQueryBuilder = $stockStatusQueryBuilder
            ?? ObjectManager::getInstance()->get(StockStatusQueryBuilder::class);
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

        $select = $this->stockStatusQueryBuilder->apply(
            $select,
            $mainTableAlias,
            'stock_index',
            'entity_id',
            $showOutOfStockFlag ? null : $stockValues
        );

        if ($type === self::FILTER_ENTITY_AND_SUB_PRODUCTS) {
            $select = $this->stockStatusQueryBuilder->apply(
                $select,
                $mainTableAlias,
                'sub_products_stock_index',
                'source_id',
                $showOutOfStockFlag ? null : $stockValues
            );
        }

        return $select;
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
