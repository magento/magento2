<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer\Stock;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Indexer\Stock\CacheCleaner;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\CacheContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Test for CacheCleaner
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 */
class CacheCleanerTest extends TestCase
{
    use MockCreationTrait;

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
        $this->stockConfigurationMock = $this->createPartialMockWithReflection(
            Configuration::class,
            ['getStockThresholdQty']
        );
        $this->cacheContextMock = $this->getMockBuilder(CacheContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();
        
        $this->metadataPoolMock = $this->createPartialMockWithReflection(
            MetadataPool::class,
            ['getMetadata', 'getHydrator']
        );
        
        $metadataMock = $this->createPartialMockWithReflection(
            EntityMetadata::class,
            ['getLinkField']
        );
        $metadataMock->method('getLinkField')->willReturn('row_id');
        $this->metadataPoolMock->method('getMetadata')->willReturn($metadataMock);
        
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceMock->method('getConnection')->willReturn($this->connectionMock);

        $this->unit = new CacheCleaner(
            $this->resourceMock,
            $this->stockConfigurationMock,
            $this->cacheContextMock,
            $this->eventManagerMock,
            $this->metadataPoolMock
        );
    }

    /**
     * Test clean cache by product ids and category ids
     *
     * @param bool $stockStatusBefore
     * @param bool $stockStatusAfter
     * @param int $qtyAfter
     * @param bool|int $stockThresholdQty
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    #[DataProvider('cleanDataProvider')]
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
        $this->selectMock->expects($this->exactly(7))
            ->method('where')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) {
                if ($arg1 == 'product_id IN (?)') {
                    return $this->selectMock;
                } elseif ($arg1 == 'stock_id = ?') {
                    return $this->selectMock;
                } elseif ($arg1 == 'website_id = ?') {
                    return $this->selectMock;
                } elseif ($arg1 == 'product_id IN (?)') {
                    return $this->selectMock;
                } elseif ($arg1 == 'stock_id = ?') {
                    return $this->selectMock;
                } elseif ($arg1 == 'website_id = ?') {
                    return $this->selectMock;
                } elseif ($arg1 == 'product_id IN (?)' && $arg2 == [123] && $arg3 == \Zend_Db::INT_TYPE) {
                    return $this->selectMock;
                }
            });
        $this->connectionMock->expects($this->exactly(1))
            ->method('fetchCol')
            ->willReturn([$categoryId]);
        $this->stockConfigurationMock->method('getStockThresholdQty')->willReturn($stockThresholdQty);
        $this->cacheContextMock->expects($this->exactly(2))
            ->method('registerEntities')
            ->willReturnCallback(function ($arg1, $arg2) use ($productId, $categoryId) {
                if ($arg1 == Product::CACHE_TAG && $arg2 == [$productId]) {
                    return null;
                } elseif ($arg1 == Category::CACHE_TAG && $arg2 == [$categoryId]) {
                    return null;
                }
            });
        $this->eventManagerMock->expects($this->exactly(2))
            ->method('dispatch')
            ->with('clean_cache_by_tags', ['object' => $this->cacheContextMock]);

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
     * @return void
     */
    #[DataProvider('notCleanCacheDataProvider')]
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
        $this->stockConfigurationMock->method('getStockThresholdQty')->willReturn($stockThresholdQty);
        $this->cacheContextMock->expects($this->never())
            ->method('registerEntities');
        $this->eventManagerMock->expects($this->never())
            ->method('dispatch');

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
