<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\Framework\DB\Select;
use Magento\Indexer\Model\ResourceModel\FrontendResource;
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
     * Defines strategies of how filter should be applied
     */
    const FILTER_JUST_ENTITY = 'general_filter';
    const FILTER_ENTITY_AND_SOURCE = 'filter_with_sub_products';

    /**
     * @var FrontendResource
     */
    private $indexerStockFrontendResource;

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
     * @param FrontendResource $indexerStockFrontendResource
     * @param ConditionManager $conditionManager
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        FrontendResource $indexerStockFrontendResource,
        ConditionManager $conditionManager,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry
    ) {
        $this->indexerStockFrontendResource = $indexerStockFrontendResource;
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
     * @return Select
     * @throws \InvalidArgumentException
     */
    public function apply(Select $select, $stockValues, $type)
    {
        if ($type !== self::FILTER_JUST_ENTITY && $type !== self::FILTER_ENTITY_AND_SOURCE) {
            throw new \InvalidArgumentException(sprintf('Invalid filter type: %s', $type));
        }

        $select = clone $select;
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $this->addMainStockStatusJoin($select, $stockValues, $mainTableAlias);

        if ($type === self::FILTER_ENTITY_AND_SOURCE) {
            $this->addStockStatusJoinForSubProducts($select, $stockValues, $mainTableAlias);
        }

        return $select;
    }

    /**
     * Adds filter join for products by stock status
     *
     * @param Select $select
     * @param $stockValues
     * @param $mainTableAlias
     */
    private function addMainStockStatusJoin(Select $select, $stockValues, $mainTableAlias)
    {
        $select->joinInner(
            ['stock_index' => $this->indexerStockFrontendResource->getMainTable()],
            $this->conditionManager->combineQueries(
                [
                    sprintf('stock_index.product_id = %s.entity_id', $mainTableAlias),
                    $this->conditionManager->generateCondition(
                        'stock_index.website_id',
                        '=',
                        $this->stockConfiguration->getDefaultScopeId()
                    ),
                    $this->conditionManager->generateCondition(
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
     *
     * @param Select $select
     * @param $stockValues
     * @param $mainTableAlias
     */
    private function addStockStatusJoinForSubProducts(Select $select, $stockValues, $mainTableAlias)
    {
        $select->joinInner(
            ['sub_products_stock_index' => $this->indexerStockFrontendResource->getMainTable()],
            $this->conditionManager->combineQueries(
                [
                    sprintf('sub_products_stock_index.product_id = %s.source_id', $mainTableAlias),
                    $this->conditionManager->generateCondition(
                        'sub_products_stock_index.website_id',
                        '=',
                        $this->stockConfiguration->getDefaultScopeId()
                    ),
                    $this->conditionManager->generateCondition(
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
