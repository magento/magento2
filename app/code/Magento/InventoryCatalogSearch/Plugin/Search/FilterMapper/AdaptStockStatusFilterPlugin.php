<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Plugin\Search\FilterMapper;

use Magento\CatalogSearch\Model\Search\FilterMapper\StockStatusFilter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Adapt stock status filter to multi stocks
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdaptStockStatusFilterPlugin
{
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
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param ConditionManager $conditionManager
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param ResourceConnection $resourceConnection
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        ConditionManager $conditionManager,
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        ResourceConnection $resourceConnection,
        DefaultStockProviderInterface $defaultStockProvider = null
    ) {
        $this->conditionManager = $conditionManager;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->resourceConnection = $resourceConnection;
        $this->defaultStockProvider = $defaultStockProvider ?: ObjectManager::getInstance()
            ->get(DefaultStockProviderInterface::class);
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundApply(
        StockStatusFilter $subject,
        callable $proceed,
        Select $select,
        $stockValues,
        $type,
        $showOutOfStockFlag
    ) {
        try {
            if ($this->getStockId() === $this->defaultStockProvider->getId()) {
                return $proceed($select, $stockValues, $type, $showOutOfStockFlag);
            }

            if ($type !== StockStatusFilter::FILTER_JUST_ENTITY
                && $type !== StockStatusFilter::FILTER_ENTITY_AND_SUB_PRODUCTS
            ) {
                throw new \InvalidArgumentException('Invalid filter type: ' . $type);
            }

            $mainTableAlias = $this->extractTableAliasFromSelect($select);
            $this->addProductEntityJoin($select, $mainTableAlias);
            $this->addInventoryStockJoin($select, $showOutOfStockFlag);

            if ($type === StockStatusFilter::FILTER_ENTITY_AND_SUB_PRODUCTS) {
                $this->addSubProductEntityJoin($select, $mainTableAlias);
                $this->addSubProductInventoryStockJoin($select, $showOutOfStockFlag);
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage());
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
            $condition = $this->conditionManager
                ->generateCondition('stock_index.' . IndexStructure::IS_SALABLE, '=', 1);
            $select->where($condition);
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
            $condition = $this->conditionManager
                ->generateCondition('sub_product_stock_index.' . IndexStructure::IS_SALABLE, '=', 1);
            $select->where($condition);
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
        $tableName = $this->stockIndexTableNameResolver->execute($this->getStockId());
        return $this->resourceConnection->getTableName($tableName);
    }

    /**
     * @return int
     */
    private function getStockId(): int
    {
        return (int)$this->stockResolver->execute(
            SalesChannelInterface::TYPE_WEBSITE,
            $this->storeManager->getWebsite()->getCode()
        )->getStockId();
    }
}
