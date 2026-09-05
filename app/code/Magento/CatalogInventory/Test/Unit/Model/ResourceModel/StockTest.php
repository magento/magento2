<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\ResourceModel;

use Magento\CatalogInventory\Model\Configuration as StockConfiguration;
use Magento\CatalogInventory\Model\ResourceModel\Stock;
use Magento\Framework\App\Config;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend_Db_Statement_Interface;

/**
 * Test for \Magento\CatalogInventory\Model\ResourceModel\Stock
 */
class StockTest extends TestCase
{
    private const PRODUCT_TABLE = 'testProductTable';
    private const ITEM_TABLE = 'testItemTableName';

    /**
     * @var Stock|MockObject
     */
    private $stock;

    /**
     * @var Mysql|MockObject
     */
    private $connectionMock;

    /**
     * @var Config|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var DateTime|MockObject
     */
    private $dateTimeMock;

    /**
     * @var StockConfiguration|MockObject
     */
    private $stockConfigurationMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var \Zend_Db_Statement_Interface|MockObject
     */
    private $statementMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $objectManager->getObject(Context::class);
        $this->scopeConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockConfigurationMock = $this->getMockBuilder(StockConfiguration::class)
            ->onlyMethods(['getIsQtyTypeIds', 'getDefaultScopeId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->statementMock = $this->createMock(Zend_Db_Statement_Interface::class);
        $this->stock = $this->getMockBuilder(Stock::class)
            ->onlyMethods(['getTable', 'getConnection'])
            ->setConstructorArgs(
                [
                    'context' => $this->contextMock,
                    'scopeConfig' => $this->scopeConfigMock,
                    'dateTime' => $this->dateTimeMock,
                    'stockConfiguration' => $this->stockConfigurationMock,
                    'storeManager' => $this->storeManagerMock
                ]
            )->getMock();
    }

    /**
     * Test Save Product Status per website with product ids.
     *
     * @param int $websiteId
     * @param array $productIds
     * @param array $products
     * @param array $result
     * @param array $items
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    #[DataProvider('productsDataProvider')]
    public function testLockProductsStock(
        int $websiteId,
        array $productIds,
        array $products,
        array $result,
        array $items
    ): void {
        $itemIds = [];
        foreach ($items as $item) {
            $itemIds[] = $item['item_id'];
        }
        sort($itemIds);
        $this->selectMock->expects($this->exactly(3))
            ->method('from')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 === self::ITEM_TABLE) {
                    return $this->selectMock;
                } elseif ($arg1 === ['si' => self::ITEM_TABLE]) {
                    return $this->selectMock;
                } elseif ($arg1 === ['p' => self::PRODUCT_TABLE] && empty($arg2)) {
                    return $this->selectMock;
                }
            });

        $this->selectMock->expects($this->exactly(4))
            ->method('where')
            ->willReturnCallback(function ($arg1, $arg2) use ($websiteId, $productIds, $itemIds) {
                if ($arg1 === 'website_id = ?' && $arg2 === $websiteId) {
                    return $this->selectMock;
                } elseif ($arg1 === 'product_id IN(?)' && $arg2 === $productIds) {
                    return $this->selectMock;
                } elseif ($arg1 === 'item_id IN (?)' && $arg2 === $itemIds) {
                    return $this->selectMock;
                } elseif ($arg1 === 'entity_id IN (?)' && $arg2 === $productIds) {
                    return $this->selectMock;
                }
            });
        $this->selectMock->expects($this->once())
            ->method('forUpdate')
            ->with($this->identicalTo(true))
            ->willReturnSelf();
        $this->selectMock->expects($this->once())
            ->method('columns')
            ->with($this->identicalTo(['product_id' => 'entity_id', 'type_id' => 'type_id']))
            ->willReturnSelf();
        $this->connectionMock->expects($this->exactly(3))
            ->method('select')
            ->willReturn($this->selectMock);
        $this->connectionMock->expects($this->exactly(2))
            ->method('query')
            ->with($this->identicalTo($this->selectMock))
            ->willReturn($this->statementMock);
        $this->statementMock
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls($items, $products);
        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->identicalTo($this->selectMock))
            ->willReturn($result);
        $this->stock->expects($this->exactly(2))
            ->method('getTable')
            ->willReturnCallback(function ($arg1) {
                if ($arg1 == 'cataloginventory_stock_item') {
                    return self::ITEM_TABLE;
                } elseif ($arg1 == 'catalog_product_entity') {
                    return self::PRODUCT_TABLE;
                }
            });
        $this->stock->expects($this->exactly(6))
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $lockResult = $this->stock->lockProductsStock($productIds, $websiteId);

        $this->assertEquals($result, $lockResult);
    }

    /**
     * @return array
     */
    public static function productsDataProvider(): array
    {
        return [
            [
                0,
                [1, 2, 3],
                [
                    1 => ['product_id' => 1],
                    2 => ['product_id' => 2],
                    3 => ['product_id' => 3]
                ],
                [
                    1 => [
                        'product_id' => 1,
                        'type_id' => 'simple'
                    ],
                    2 => [
                        'product_id' => 2,
                        'type_id' => 'simple'
                    ],
                    3 => [
                        'product_id' => 3,
                        'type_id' => 'simple'
                    ],
                ],
                [['item_id' => 1], ['item_id' => 2], ['item_id' => 3]]
            ],
            'item ids returned out of order' => [
                0,
                [1, 2, 3],
                [
                    1 => ['product_id' => 1],
                    2 => ['product_id' => 2],
                    3 => ['product_id' => 3]
                ],
                [
                    1 => [
                        'product_id' => 1,
                        'type_id' => 'simple'
                    ],
                    2 => [
                        'product_id' => 2,
                        'type_id' => 'simple'
                    ],
                    3 => [
                        'product_id' => 3,
                        'type_id' => 'simple'
                    ],
                ],
                [['item_id' => 3], ['item_id' => 1], ['item_id' => 2]]
            ]
        ];
    }

    /**
     * Stock item rows must be addressed by primary key, in ascending order.
     *
     * @return void
     */
    public function testCorrectItemsQtyUpdatesRowsByItemId(): void
    {
        $this->stock->method('getTable')
            ->with('cataloginventory_stock_item')
            ->willReturn(self::ITEM_TABLE);
        $this->stock->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $whereCalls = [];
        $this->selectMock->method('from')->willReturnSelf();
        $this->selectMock->method('where')
            ->willReturnCallback(function ($condition, $value = null) use (&$whereCalls) {
                $whereCalls[$condition] = $value;
                return $this->selectMock;
            });

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->identicalTo($this->selectMock))
            ->willReturn(
                [
                    ['item_id' => '40', 'product_id' => '3'],
                    ['item_id' => '10', 'product_id' => '1'],
                ]
            );
        $this->connectionMock->method('quoteInto')
            ->willReturnCallback(
                static fn ($text, $value) => str_replace('?', (string)$value, $text)
            );

        $caseArguments = [];
        $this->connectionMock->expects($this->once())
            ->method('getCaseSql')
            ->willReturnCallback(
                function ($valueName, $casesResults, $defaultValue) use (&$caseArguments) {
                    $caseArguments = [$valueName, $casesResults, $defaultValue];
                    return new \Zend_Db_Expr('CASE');
                }
            );

        $updateArguments = [];
        $this->connectionMock->expects($this->once())
            ->method('update')
            ->willReturnCallback(
                function ($table, $bind, $where) use (&$updateArguments) {
                    $updateArguments = [$table, $bind, $where];
                    return 2;
                }
            );
        $this->connectionMock->expects($this->once())->method('beginTransaction');
        $this->connectionMock->expects($this->once())->method('commit');

        $this->stock->correctItemsQty([3 => 5, 1 => 2], 1, '-');

        $this->assertSame(['website_id = ?' => 1, 'product_id IN(?)' => [3, 1]], $whereCalls);
        $this->assertSame('item_id', $caseArguments[0]);
        $this->assertSame([10, 40], array_keys($caseArguments[1]));
        $this->assertSame(['qty-2', 'qty-5'], array_values($caseArguments[1]));
        $this->assertSame('qty', $caseArguments[2]);
        $this->assertSame(self::ITEM_TABLE, $updateArguments[0]);
        $this->assertSame(['item_id IN (?)' => [10, 40]], $updateArguments[2]);
    }

    /**
     * No stock item row for the given products means nothing to update and nothing to lock.
     *
     * @return void
     */
    public function testCorrectItemsQtyWithoutMatchingStockItems(): void
    {
        $this->stock->method('getTable')
            ->with('cataloginventory_stock_item')
            ->willReturn(self::ITEM_TABLE);
        $this->stock->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->selectMock->method('from')->willReturnSelf();
        $this->selectMock->method('where')->willReturnSelf();
        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);
        $this->connectionMock->expects($this->never())->method('update');
        $this->connectionMock->expects($this->never())->method('beginTransaction');
        $this->connectionMock->expects($this->never())->method('commit');

        $this->stock->correctItemsQty([3 => 5], 1, '-');
    }
}
