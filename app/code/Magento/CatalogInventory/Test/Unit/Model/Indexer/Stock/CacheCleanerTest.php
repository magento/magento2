<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer\Stock;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\CacheCleaner;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for CacheCleaner
 */
class CacheCleanerTest extends TestCase
{
    /**
     * @var CacheCleaner
     */
    private $unit;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var CacheContext|MockObject
     */
    private $cacheContextMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var StockConfigurationInterface|MockObject
     */
    private $stockConfigurationMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    protected function setUp(): void
    {
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $this->stockConfigurationMock = $this->getMockBuilder(StockConfigurationInterface::class)
            ->addMethods(['getStockThresholdQty'])
            ->getMockForAbstractClass();
        $this->cacheContextMock = $this->getMockBuilder(CacheContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->addMethods(['getLinkField'])
            ->onlyMethods(['getMetadata'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->unit = (new ObjectManager($this))->getObject(
            CacheCleaner::class,
            [
                'resource' => $this->resourceMock,
                'stockConfiguration' => $this->stockConfigurationMock,
                'cacheContext' => $this->cacheContextMock,
                'eventManager' => $this->eventManagerMock,
                'metadataPool' => $this->metadataPoolMock
            ]
        );
    }

    /**
     * Test clean cache by product ids and category ids
     *
     * @param bool $stockStatusBefore
     * @param bool $stockStatusAfter
     * @param int $qtyAfter
     * @param bool|int $stockThresholdQty
     * @dataProvider cleanDataProvider
     * @return void
     */
    public function testClean($stockStatusBefore, $stockStatusAfter, $qtyAfter, $stockThresholdQty): void
    {
        $productId = 123;
        $categoryId = 3;
        $this->selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $this->selectMock->expects($this->any())
            ->method('joinLeft')
            ->willReturnSelf();
        $this->connectionMock->expects($this->exactly(3))
            ->method('select')
            ->willReturn($this->selectMock);
        $this->connectionMock->expects($this->exactly(2))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [
                    ['product_id' => $productId, 'stock_status' => $stockStatusBefore],
                ],
                [
                    ['product_id' => $productId, 'stock_status' => $stockStatusAfter, 'qty' => $qtyAfter],
                ]
            );
        $this->connectionMock->expects($this->exactly(3))
            ->method('select')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->exactly(7))
            ->method('where')
            ->withConsecutive(
                ['product_id IN (?)'],
                ['stock_id = ?'],
                ['website_id = ?'],
                ['product_id IN (?)'],
                ['stock_id = ?'],
                ['website_id = ?'],
                ['product_id IN (?)', [123], \Zend_Db::INT_TYPE]
            )
            ->willReturnSelf();
        $this->connectionMock->expects($this->exactly(1))
            ->method('fetchCol')
            ->willReturn([$categoryId]);
        $this->stockConfigurationMock->expects($this->once())
            ->method('getStockThresholdQty')
            ->willReturn($stockThresholdQty);
        $this->cacheContextMock->expects($this->exactly(2))
            ->method('registerEntities')
            ->withConsecutive(
                [Product::CACHE_TAG, [$productId]],
                [Category::CACHE_TAG, [$categoryId]],
            );
        $this->eventManagerMock->expects($this->exactly(2))
            ->method('dispatch')
            ->with('clean_cache_by_tags', ['object' => $this->cacheContextMock]);
        $this->metadataPoolMock->expects($this->exactly(2))
            ->method('getMetadata')
            ->willReturnSelf();
        $this->metadataPoolMock->expects($this->exactly(2))
            ->method('getLinkField')
            ->willReturn('row_id');

        $callback = function () {
        };
        $this->unit->clean([], $callback);
    }

    /**
     * @return array
     */
    public static function cleanDataProvider(): array
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
     * @return void
     */
    public function testNotCleanCache($stockStatusBefore, $stockStatusAfter, $qtyAfter, $stockThresholdQty): void
    {
        $productId = 123;
        $this->selectMock->expects($this->any())->method('from')
            ->willReturnSelf();
        $this->selectMock->expects($this->any())->method('where')
            ->willReturnSelf();
        $this->selectMock->expects($this->any())->method('joinLeft')
            ->willReturnSelf();
        $this->connectionMock->expects($this->exactly(2))
            ->method('select')
            ->willReturn($this->selectMock);
        $this->connectionMock->expects($this->exactly(2))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [
                    ['product_id' => $productId, 'stock_status' => $stockStatusBefore],
                ],
                [
                    ['product_id' => $productId, 'stock_status' => $stockStatusAfter, 'qty' => $qtyAfter],
                ]
            );
        $this->stockConfigurationMock->expects($this->once())
            ->method('getStockThresholdQty')
            ->willReturn($stockThresholdQty);
        $this->cacheContextMock->expects($this->never())
            ->method('registerEntities');
        $this->eventManagerMock->expects($this->never())
            ->method('dispatch');
        $this->metadataPoolMock->expects($this->exactly(2))
            ->method('getMetadata')
            ->willReturnSelf();
        $this->metadataPoolMock->expects($this->exactly(2))
            ->method('getLinkField')
            ->willReturn('row_id');

        $callback = function () {
        };
        $this->unit->clean([], $callback);
    }

    /**
     * @return array
     */
    public static function notCleanCacheDataProvider(): array
    {
        return [
            [true, true, 1, false],
            [false, false, 1, false],
            [true, true, 3, 2],
        ];
    }
}
