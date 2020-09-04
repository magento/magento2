<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogInventory\Model\ResourceModel\Stock
 */
class StockTest extends TestCase
{
    const PRODUCT_TABLE = 'testProductTable';
    const ITEM_TABLE = 'testItemTableName';

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
            ->setMethods(['getIsQtyTypeIds', 'getDefaultScopeId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->connectionMock = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->statementMock = $this->getMockForAbstractClass(\Zend_Db_Statement_Interface::class);
        $this->stock = $this->getMockBuilder(Stock::class)
            ->setMethods(['getTable', 'getConnection'])
            ->setConstructorArgs(
                [
                    'context' => $this->contextMock,
                    'scopeConfig' => $this->scopeConfigMock,
                    'dateTime' => $this->dateTimeMock,
                    'stockConfiguration' => $this->stockConfigurationMock,
                    'storeManager' => $this->storeManagerMock,
                ]
            )->getMock();
    }

    /**
     * Test Save Product Status per website with product ids.
     *
     * @dataProvider productsDataProvider
     * @param int $websiteId
     * @param array $productIds
     * @param array $products
     * @param array $result
     * @param array $items
     *
     * @return void
     */
    public function testLockProductsStock(
        int $websiteId,
        array $productIds,
        array $products,
        array $result,
        array $items
    ) {
        $itemIds = [];
        foreach ($items as $item) {
            $itemIds[] = $item['item_id'];
        }
        $this->selectMock->expects($this->exactly(3))
            ->method('from')
            ->withConsecutive(
                [$this->identicalTo(self::ITEM_TABLE)],
                [$this->identicalTo(['si' => self::ITEM_TABLE])],
                [$this->identicalTo(['p' => self::PRODUCT_TABLE]), $this->identicalTo([])]
            )
            ->willReturnSelf();
        $this->selectMock->expects($this->exactly(4))
            ->method('where')
            ->withConsecutive(
                [$this->identicalTo('website_id = ?'), $this->identicalTo($websiteId)],
                [$this->identicalTo('product_id IN(?)'), $this->identicalTo($productIds)],
                [$this->identicalTo('item_id IN (?)'), $this->identicalTo($itemIds)],
                [$this->identicalTo('entity_id IN (?)'), $this->identicalTo($productIds)]
            )
            ->willReturnSelf();
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
        $this->statementMock->expects($this->at(0))
            ->method('fetchAll')
            ->willReturn($items);
        $this->statementMock->expects($this->at(1))
            ->method('fetchAll')
            ->willReturn($products);
        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->identicalTo($this->selectMock))
            ->willReturn($result);
        $this->stock->expects($this->exactly(2))
            ->method('getTable')
            ->withConsecutive(
                [$this->identicalTo('cataloginventory_stock_item')],
                [$this->identicalTo('catalog_product_entity')]
            )->will($this->onConsecutiveCalls(
                self::ITEM_TABLE,
                self::PRODUCT_TABLE
            ));
        $this->stock->expects($this->exactly(6))
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $lockResult = $this->stock->lockProductsStock($productIds, $websiteId);

        $this->assertEquals($result, $lockResult);
    }

    /**
     * @return array
     */
    public function productsDataProvider(): array
    {
        return [
            [
                0,
                [1, 2, 3],
                [
                    1 => ['product_id' => 1],
                    2 => ['product_id' => 2],
                    3 => ['product_id' => 3],
                ],
                [
                    1 => [
                        'product_id' => 1,
                        'type_id' => 'simple',
                    ],
                    2 => [
                        'product_id' => 2,
                        'type_id' => 'simple',
                    ],
                    3 => [
                        'product_id' => 3,
                        'type_id' => 'simple',
                    ],
                ],
                [['item_id' => 1], ['item_id' => 2], ['item_id' => 3]]
            ],
        ];
    }
}
