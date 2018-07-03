<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\DB\Select;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock;

class StockStatusFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Const to define if 'show out of stock' configuration flag enabled or not
     */
    const SHOW_OUT_OF_STOCK_ENABLED = true;
    const SHOW_OUT_OF_STOCK_DISABLED = false;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var ResourceConnection */
    private $resource;

    /** @var ConditionManager */
    private $conditionManager;

    /** @var StockConfigurationInterface */
    private $stockConfiguration;

    /** @var StockRegistryInterface */
    private $stockRegistry;

    /** @var StockStatusFilter */
    private $stockStatusFilter;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->resource = $this->objectManager->create(ResourceConnection::class);
        $this->conditionManager = $this->objectManager->create(ConditionManager::class);
        $this->stockConfiguration = $this->objectManager->create(StockConfigurationInterface::class);
        $this->stockRegistry = $this->objectManager->create(StockRegistryInterface::class);
        $this->stockStatusFilter = $this->objectManager->create(StockStatusFilter::class);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid filter type: Luke I am your father!
     */
    public function testApplyWithWrongType()
    {
        $select = $this->resource->getConnection()->select();
        $this->stockStatusFilter->apply(
            $select,
            Stock::STOCK_IN_STOCK,
            'Luke I am your father!',
            self::SHOW_OUT_OF_STOCK_ENABLED
        );
    }

    public function testApplyGeneralFilterWithOutOfStock()
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        );

        $resultSelect = $this->stockStatusFilter->apply(
            $select,
            Stock::STOCK_IN_STOCK,
            StockStatusFilter::FILTER_JUST_ENTITY,
            self::SHOW_OUT_OF_STOCK_ENABLED
        );

        $expectedSelect = $this->getExpectedSelectForGeneralFilter(self::SHOW_OUT_OF_STOCK_ENABLED);

        $this->assertEquals(
            (string) $expectedSelect,
            (string) $resultSelect,
            'Select queries must be the same'
        );
    }

    public function testApplyGeneralFilterWithoutOutOfStock()
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        );

        $resultSelect = $this->stockStatusFilter->apply(
            $select,
            Stock::STOCK_IN_STOCK,
            StockStatusFilter::FILTER_JUST_ENTITY,
            self::SHOW_OUT_OF_STOCK_DISABLED
        );

        $expectedSelect = $this->getExpectedSelectForGeneralFilter(self::SHOW_OUT_OF_STOCK_DISABLED);

        $this->assertEquals(
            (string) $expectedSelect,
            (string) $resultSelect,
            'Select queries must be the same'
        );
    }

    public function testApplyFullFilterWithOutOfStock()
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        );

        $resultSelect = $this->stockStatusFilter->apply(
            $select,
            Stock::STOCK_IN_STOCK,
            StockStatusFilter::FILTER_ENTITY_AND_SUB_PRODUCTS,
            self::SHOW_OUT_OF_STOCK_ENABLED
        );

        $expectedSelect = $this->getExpectedSelectForFullFilter(self::SHOW_OUT_OF_STOCK_ENABLED);

        $this->assertEquals(
            (string) $expectedSelect,
            (string) $resultSelect,
            'Select queries must be the same'
        );
    }

    public function testApplyFullFilterWithoutOutOfStock()
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        );

        $resultSelect = $this->stockStatusFilter->apply(
            $select,
            Stock::STOCK_IN_STOCK,
            StockStatusFilter::FILTER_ENTITY_AND_SUB_PRODUCTS,
            self::SHOW_OUT_OF_STOCK_DISABLED
        );

        $expectedSelect = $this->getExpectedSelectForFullFilter(self::SHOW_OUT_OF_STOCK_DISABLED);

        $this->assertEquals(
            (string) $expectedSelect,
            (string) $resultSelect,
            'Select queries must be the same'
        );
    }

    /**
     * @param bool $withOutOfStock
     * @return Select
     */
    private function getExpectedSelectForGeneralFilter($withOutOfStock)
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        )->joinInner(
            ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
            $this->conditionManager->combineQueries(
                [
                    'stock_index.product_id = some_index.entity_id',
                    $this->conditionManager->generateCondition(
                        'stock_index.website_id',
                        '=',
                        $this->stockConfiguration->getDefaultScopeId()
                    ),
                    $withOutOfStock
                        ? ''
                        : $this->conditionManager->generateCondition(
                            'stock_index.stock_status',
                            '=',
                            Stock::STOCK_IN_STOCK
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

        return $select;
    }

    /**
     * @param bool $withOutOfStock
     * @return Select
     */
    private function getExpectedSelectForFullFilter($withOutOfStock)
    {
        $select = $this->getExpectedSelectForGeneralFilter($withOutOfStock);
        $select->joinInner(
            ['sub_products_stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
            $this->conditionManager->combineQueries(
                [
                    'sub_products_stock_index.product_id = some_index.source_id',
                    $this->conditionManager->generateCondition(
                        'sub_products_stock_index.website_id',
                        '=',
                        $this->stockConfiguration->getDefaultScopeId()
                    ),
                    $withOutOfStock
                        ? ''
                        : $this->conditionManager->generateCondition(
                            'sub_products_stock_index.stock_status',
                            '=',
                            Stock::STOCK_IN_STOCK
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

        return $select;
    }
}
