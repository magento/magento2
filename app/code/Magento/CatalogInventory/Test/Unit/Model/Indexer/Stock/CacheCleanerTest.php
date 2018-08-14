<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer\Stock;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\CacheCleaner;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\Product;

class CacheCleanerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CacheCleaner
     */
    private $unit;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var CacheContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheContextMock;

    /**
     * @var StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockConfigurationMock;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    protected function setUp()
    {
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)->disableOriginalConstructor()->getMock();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)->getMock();
        $this->stockConfigurationMock = $this->getMockBuilder(StockConfigurationInterface::class)
            ->setMethods(['getStockThresholdQty'])->getMockForAbstractClass();
        $this->cacheContextMock = $this->getMockBuilder(CacheContext::class)->disableOriginalConstructor()->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)->getMock();
        $this->selectMock = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connectionMock));

        $this->unit = (new ObjectManager($this))->getObject(
            CacheCleaner::class,
            [
                'resource' => $this->resourceMock,
                'stockConfiguration' => $this->stockConfigurationMock,
                'cacheContext' => $this->cacheContextMock,
                'eventManager' => $this->eventManagerMock
            ]
        );
    }

    /**
     * @param bool $stockStatusBefore
     * @param bool $stockStatusAfter
     * @param int $qtyAfter
     * @param bool|int $stockThresholdQty
     * @dataProvider cleanDataProvider
     */
    public function testClean($stockStatusBefore, $stockStatusAfter, $qtyAfter, $stockThresholdQty)
    {
        $productId = 123;
        $this->selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('where')->willReturnSelf();
        $this->connectionMock->expects($this->exactly(2))->method('select')->willReturn($this->selectMock);
        $this->connectionMock->expects($this->exactly(2))->method('fetchAll')->willReturnOnConsecutiveCalls(
            [
                ['product_id' => $productId, 'stock_status' => $stockStatusBefore],
            ],
            [
                ['product_id' => $productId, 'stock_status' => $stockStatusAfter, 'qty' => $qtyAfter],
            ]
        );
        $this->stockConfigurationMock->expects($this->once())->method('getStockThresholdQty')
            ->willReturn($stockThresholdQty);
        $this->cacheContextMock->expects($this->once())->method('registerEntities')
            ->with(Product::CACHE_TAG, [$productId]);
        $this->eventManagerMock->expects($this->once())->method('dispatch')
            ->with('clean_cache_by_tags', ['object' => $this->cacheContextMock]);

        $callback = function () {
        };
        $this->unit->clean([], $callback);
    }

    /**
     * @return array
     */
    public function cleanDataProvider()
    {
        return [
            [true, false, 1, false],
            [false, true, 1, false],
            [true, true, 1, 2],
            [false, false, 1, 2],
        ];
    }

    /**
     * @param bool $stockStatusBefore
     * @param bool $stockStatusAfter
     * @param int $qtyAfter
     * @param bool|int $stockThresholdQty
     * @dataProvider notCleanCacheDataProvider
     */
    public function testNotCleanCache($stockStatusBefore, $stockStatusAfter, $qtyAfter, $stockThresholdQty)
    {
        $productId = 123;
        $this->selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->selectMock->expects($this->any())->method('where')->willReturnSelf();
        $this->connectionMock->expects($this->exactly(2))->method('select')->willReturn($this->selectMock);
        $this->connectionMock->expects($this->exactly(2))->method('fetchAll')->willReturnOnConsecutiveCalls(
            [
                ['product_id' => $productId, 'stock_status' => $stockStatusBefore],
            ],
            [
                ['product_id' => $productId, 'stock_status' => $stockStatusAfter, 'qty' => $qtyAfter],
            ]
        );
        $this->stockConfigurationMock->expects($this->once())->method('getStockThresholdQty')
            ->willReturn($stockThresholdQty);
        $this->cacheContextMock->expects($this->never())->method('registerEntities');
        $this->eventManagerMock->expects($this->never())->method('dispatch');

        $callback = function () {
        };
        $this->unit->clean([], $callback);
    }

    /**
     * @return array
     */
    public function notCleanCacheDataProvider()
    {
        return [
            [true, true, 1, false],
            [false, false, 1, false],
            [true, true, 3, 2],
        ];
    }
}
