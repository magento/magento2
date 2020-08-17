<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Indexer\ProductPriceIndexFilter;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Product Price filter test, to ensure that product id's filtered.
 */
class ProductPriceIndexFilterTest extends TestCase
{

    /**
     * @var MockObject|StockConfigurationInterface $stockConfiguration
     */
    private $stockConfiguration;

    /**
     * @var MockObject|Item $item
     */
    private $item;

    /**
     * @var MockObject|ResourceConnection $resourceCnnection
     */
    private $resourceCnnection;

    /**
     * @var MockObject|Generator $generator
     */
    private $generator;

    /**
     * @var ProductPriceIndexFilter $productPriceIndexFilter
     */
    private $productPriceIndexFilter;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->stockConfiguration = $this->getMockForAbstractClass(StockConfigurationInterface::class);
        $this->item = $this->createMock(Item::class);
        $this->resourceCnnection = $this->createMock(ResourceConnection::class);
        $this->generator = $this->createMock(Generator::class);

        $this->productPriceIndexFilter = new ProductPriceIndexFilter(
            $this->stockConfiguration,
            $this->item,
            $this->resourceCnnection,
            'indexer',
            $this->generator,
            100
        );
    }

    /**
     * Test to ensure that Modify Price method uses entityIds,
     */
    public function testModifyPrice()
    {
        $entityIds = [1, 2, 3];
        $indexTableStructure = $this->createMock(IndexTableStructure::class);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceCnnection->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $selectMock = $this->createMock(Select::class);
        $connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $selectMock->expects($this->at(2))
            ->method('where')
            ->with('stock_item.product_id in (?)', $entityIds)
            ->willReturn($selectMock);
        $this->generator->expects($this->once())
            ->method('generate')
            ->willReturnCallback(
                $this->getBatchIteratorCallback($selectMock, 5)
            );

        $fetchStmtMock = $this->createPartialMock(\Zend_Db_Statement_Pdo::class, ['fetchAll']);
        $fetchStmtMock->expects($this->any())
            ->method('fetchAll')
            ->willReturn([['product_id' => 1]]);
        $connectionMock->expects($this->any())->method('query')->willReturn($fetchStmtMock);
        $this->productPriceIndexFilter->modifyPrice($indexTableStructure, $entityIds);
    }

    /**
     * Returns batches.
     *
     * @param MockObject $selectMock
     * @param int $batchCount
     * @return \Closure
     */
    private function getBatchIteratorCallback(MockObject $selectMock, int $batchCount): \Closure
    {
        $iteratorCallback = function () use ($batchCount, $selectMock): array {
            $result = [];
            $count = $batchCount;
            while ($count) {
                $count--;
                $result[$count] = $selectMock;
            }

            return $result;
        };

        return $iteratorCallback;
    }
}
