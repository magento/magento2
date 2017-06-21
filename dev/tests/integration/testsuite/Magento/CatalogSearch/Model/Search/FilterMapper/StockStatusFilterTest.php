<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\DB\Select;
use Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\FrontendResource;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock;

class StockStatusFilterTest extends \PHPUnit_Framework_TestCase
{
    private $objectManager;

    private $resource;

    private $conditionManager;

    private $indexerStockFrontendResource;

    private $stockConfiguration;

    private $stockRegistry;

    private $stockStatusFilter;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->resource = $this->objectManager->create(ResourceConnection::class);
        $this->conditionManager = $this->objectManager->create(ConditionManager::class);
        $this->indexerStockFrontendResource = $this->objectManager->create(FrontendResource::class);
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
            'Luke I am your father!'
        );
    }

    public function testApplyWithGeneralFilter()
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        );

        $resultSelect = $this->stockStatusFilter->apply(
            $select,
            Stock::STOCK_IN_STOCK,
            StockStatusFilter::FILTER_JUST_ENTITY
        );

        $expectedSelect = $this->getExpectedSelectForGeneralFilter();

        $this->assertEquals(
            (string) $expectedSelect,
            (string) $resultSelect,
            'Select queries must be the same'
        );
    }

    public function testApplyWithFullFilter()
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        );

        $resultSelect = $this->stockStatusFilter->apply(
            $select,
            Stock::STOCK_IN_STOCK,
            StockStatusFilter::FILTER_ENTITY_AND_SOURCE
        );

        $expectedSelect = $this->getExpectedSelectForFullFilter();

        $this->assertEquals(
            (string) $expectedSelect,
            (string) $resultSelect,
            'Select queries must be the same'
        );
    }

    private function getExpectedSelectForGeneralFilter()
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
            ['some_index' => 'some_table'],
            ['entity_id' => 'entity_id']
        )->joinInner(
            ['stock_index' => $this->indexerStockFrontendResource->getMainTable()],
            $this->conditionManager->combineQueries(
                [
                    'stock_index.product_id = some_index.entity_id',
                    $this->conditionManager->generateCondition(
                        'stock_index.website_id',
                        '=',
                        $this->stockConfiguration->getDefaultScopeId()
                    ),
                    $this->conditionManager->generateCondition(
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

    private function getExpectedSelectForFullFilter()
    {
        $select = $this->getExpectedSelectForGeneralFilter();
        $select->joinInner(
            ['sub_products_stock_index' => $this->indexerStockFrontendResource->getMainTable()],
            $this->conditionManager->combineQueries(
                [
                    'sub_products_stock_index.product_id = some_index.source_id',
                    $this->conditionManager->generateCondition(
                        'sub_products_stock_index.website_id',
                        '=',
                        $this->stockConfiguration->getDefaultScopeId()
                    ),
                    $this->conditionManager->generateCondition(
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
