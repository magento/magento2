<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Plugin\Search\FilterMapper;

use InvalidArgumentException;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\CatalogSearch\Model\Search\FilterMapper\StockStatusFilter;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\MultiDimensionalIndexer\Alias;
use Magento\Framework\MultiDimensionalIndexer\IndexNameBuilder;
use Magento\Framework\MultiDimensionalIndexer\IndexNameResolverInterface;

/**
 * Adapt stock status filter to multi stocks
 */
class AdaptStockStatusFilterPlugin
{
    /**
     * Stock table names and aliases
     */
    const TABLE_ALIAS_STOCK_INDEX = 'stock_index';

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexNameResolverInterface
     */
    private $indexNameResolver;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ConditionManager $conditionManager
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexNameResolverInterface $indexNameResolver
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ConditionManager $conditionManager,
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver,
        IndexNameBuilder $indexNameBuilder,
        IndexNameResolverInterface $indexNameResolver,
        ResourceConnection $resourceConnection
    ) {
        $this->conditionManager = $conditionManager;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexNameResolver = $indexNameResolver;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param StockStatusFilter $subject
     * @param callable $proceed
     * @param Select $select
     * @param array $stockValues
     * @param string $type
     * @param bool $showOutOfStockFlag
     * @return Select
     * @throws \InvalidArgumentException
     */
    public function aroundApply(
        StockStatusFilter $subject,
        callable $proceed,
        Select $select,
        $stockValues,
        $type,
        $showOutOfStockFlag
    ) {
        if ($type !== StockStatusFilter::FILTER_JUST_ENTITY
            && $type !== StockStatusFilter::FILTER_ENTITY_AND_SUB_PRODUCTS
        ) {
            throw new InvalidArgumentException('Invalid filter type: ' . $type);
        }

        $select = clone $select;
        $mainTableAlias = $this->extractTableAliasFromSelect($select);
        $this->addProductEntityJoin($select, $mainTableAlias);
        $this->addInventoryStockJoin($select, $showOutOfStockFlag);

        if ($type === StockStatusFilter::FILTER_ENTITY_AND_SUB_PRODUCTS) {
            $this->addSubProductEntityJoin($select, $mainTableAlias);
            $this->addSubProductInventoryStockJoin($select, $showOutOfStockFlag);
        }

        return $select;
    }

    /**
     * @param Select $select
     * @param string $mainTableAlias
     */
    private function addProductEntityJoin(Select $select, $mainTableAlias)
    {
        $select->joinInner(
            ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
            sprintf('product.entity_id = %s.entity_id', $mainTableAlias),
            []
        );
    }

    /**
     * @param Select $select
     * @param string $mainTableAlias
     */
    private function addSubProductEntityJoin(Select $select, $mainTableAlias)
    {
        $select->joinInner(
            ['sub_product' => $this->resourceConnection->getTableName('catalog_product_entity')],
            sprintf('sub_product.entity_id = %s.source_id', $mainTableAlias),
            []
        );
    }

    /**
     * @param Select $select
     * @param bool $showOutOfStockFlag
     * @return void
     */
    private function addInventoryStockJoin(Select $select, $showOutOfStockFlag)
    {
        $select->joinInner(
            ['stock_index' => $this->getStockTableName()],
            'stock_index.sku = product.sku',
            []
        );
        if ($showOutOfStockFlag === false) {
            $select->where($this->conditionManager->generateCondition('stock_index.quantity', '>', 0));
        }
    }

    /**
     * @param Select $select
     * @param bool $showOutOfStockFlag
     * @return void
     */
    private function addSubProductInventoryStockJoin(Select $select, bool $showOutOfStockFlag)
    {
        $select->joinInner(
            ['sub_product_stock_index' => $this->getStockTableName()],
            'sub_product_stock_index.sku = sub_product.sku',
            []
        );
        if ($showOutOfStockFlag === false) {
            $select->where($this->conditionManager->generateCondition('sub_product_stock_index.quantity', '>', 0));
        }
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

    /**
     * @return string
     */
    private function getStockTableName(): string
    {
        $website = $this->storeManager->getWebsite();
        $stock = $this->stockResolver->get(
            SalesChannelInterface::TYPE_WEBSITE,
            $website->getCode()
        );
        $indexName = $this->indexNameBuilder
            ->setIndexId('inventory_stock')
            ->addDimension('stock_', (string)$stock->getStockId())
            ->setAlias(Alias::ALIAS_MAIN)
            ->build();
        $tableName = $this->indexNameResolver->resolveName($indexName);
        return $this->resourceConnection->getTableName($tableName);
    }
}
