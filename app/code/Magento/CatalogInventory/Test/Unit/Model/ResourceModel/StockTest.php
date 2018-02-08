<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Configuration as StockConfiguration;
use Magento\CatalogInventory\Model\ResourceModel\Stock;
use Magento\Framework\App\Config;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StockTest covers Magento\CatalogInventory\Model\ResourceModel\Stock
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockTest extends \PHPUnit_Framework_TestCase
{
    const PRODUCT_TABLE = 'testProductTable';
    const ITEM_TABLE = 'testItemTableName';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Stock
     */
    protected $stock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Mysql
     */
    protected $connectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Config
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DateTime
     */
    protected $dateTimeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StockConfiguration
     */
    protected $stockConfigurationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Context
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DB\Select
     */
    protected $selectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend_Db_Statement_Interface
     */
    protected $statementMock;

    /**
     * Prepare subjects for tests.
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
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
            ->getMock();
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

        parent::setUp();
    }

    /**
     * Test Save Product Status per website with product ids.
     *
     * @return void
     */
    public function testLockProductsStock()
    {
        $websiteId = 0;
        $productIds = [1, 2, 3];
        $result = [
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
            ]
        ];

        $this->selectMock->expects(self::exactly(2))
            ->method('from')
            ->withConsecutive(
                [self::identicalTo(['si' => self::ITEM_TABLE])],
                [self::identicalTo(['p' => self::PRODUCT_TABLE]), self::identicalTo([])]
            )
            ->willReturnSelf();
        $this->selectMock->expects(self::exactly(3))
            ->method('where')
            ->withConsecutive(
                [self::identicalTo('website_id = ?'), self::identicalTo($websiteId)],
                [self::identicalTo('product_id IN(?)'), self::identicalTo($productIds)],
                [self::identicalTo('entity_id IN (?)'), self::identicalTo($productIds)]
            )
            ->willReturnSelf();
        $this->selectMock->expects(self::once())
            ->method('forUpdate')
            ->with(self::identicalTo(true))
            ->willReturnSelf();
        $this->selectMock->expects(self::once())
            ->method('columns')
            ->with(self::identicalTo(['product_id' => 'entity_id', 'type_id' => 'type_id']))
            ->willReturnSelf();

        $this->connectionMock->expects(self::exactly(2))
            ->method('select')
            ->willReturn($this->selectMock);
        $this->connectionMock->expects(self::once())
            ->method('query')
            ->with(self::identicalTo($this->selectMock))
            ->willReturn($this->statementMock);
        $this->statementMock->expects(self::once())
            ->method('fetchAll')
            ->willReturn([
                1 => ['product_id' => 1],
                2 => ['product_id' => 2],
                3 => ['product_id' => 3]
            ]);

        $this->connectionMock->expects(self::once())
            ->method('fetchAll')
            ->with(self::identicalTo($this->selectMock))
            ->willReturn($result);

        $this->stock->expects(self::exactly(2))
            ->method('getTable')
            ->withConsecutive(
                [self::identicalTo('cataloginventory_stock_item')],
                [self::identicalTo('catalog_product_entity')]
            )->will(self::onConsecutiveCalls(
                self::ITEM_TABLE,
                self::PRODUCT_TABLE
            ));
        $this->stock->expects(self::exactly(4))
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $lockResult = $this->stock->lockProductsStock($productIds, $websiteId);

        self::assertEquals($result, $lockResult);
    }

    /**
     * Save Product Status per website without product ids.
     *
     * @return void
     */
    public function testLockProductsStockWithoutIds()
    {
        $productIds = [];
        $websiteId = 0;
        $lockResult = $this->stock->lockProductsStock($productIds, $websiteId);

        self::assertEquals([], $lockResult);
    }

    /**
     * Test correct particular stock products qty based on operator.
     *
     * @return void
     */
    public function testCorrectItemsQty()
    {
        $items = ['1' => 1, '2' => 2];
        $websiteId = 0;
        $operator = 'testOperator';
        $case = ['testCase1', 'testCase2'];
        $result = ['testResult1', 'testresult2'];
        $conditions = [
            $case[0] => $result[0],
            $case[1] => $result[1],
        ];
        $value = 'testValue';
        $where = ['product_id IN (?)' => [1, 2], 'website_id = ?' => $websiteId];

        $this->connectionMock->expects(self::exactly(4))
            ->method('quoteInto')
            ->withConsecutive(
                [self::identicalTo('?'), self::identicalTo(1)],
                [self::identicalTo("qty{$operator}?"), self::identicalTo(1)],
                [self::identicalTo('?'), self::identicalTo(2)],
                [self::identicalTo("qty{$operator}?"), self::identicalTo(2)]
            )->will(
                self::onConsecutiveCalls(
                    $case[0],
                    $result[0],
                    $case[1],
                    $result[1]
                )
            );
        $this->connectionMock->expects(self::once())
            ->method('getCaseSql')
            ->with(self::identicalTo('product_id'), self::identicalTo($conditions), self::identicalTo('qty'))
            ->willReturn($value);
        $this->connectionMock->expects(self::once())
            ->method('beginTransaction')
            ->willReturnSelf();
        $this->connectionMock->expects(self::once())
            ->method('update')
            ->with(self::identicalTo(self::ITEM_TABLE), self::identicalTo(['qty' => $value]), self::identicalTo($where))
            ->willReturnSelf();
        $this->connectionMock->expects(self::once())
            ->method('commit')
            ->willReturnSelf();

        $this->stock->expects(self::once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->stock->expects(self::once())
            ->method('getTable')
            ->with(self::identicalTo('cataloginventory_stock_item'))
            ->willReturn(self::ITEM_TABLE);

        $this->stock->correctItemsQty($items, $websiteId, $operator);
    }

    /**
     * Test set items out of stock basing on their quantities and config settings.
     *
     * @return void
     */
    public function testUpdateSetOutOfStock()
    {
        $string = 'testString';
        $where = 'website_id = 0 AND is_in_stock = 1 AND ' .
            '((use_config_manage_stock = 1 AND 1 = 1) OR ' .
            '(use_config_manage_stock = 0 AND manage_stock = 1)) AND ' .
            '((use_config_backorders = 1 AND 0 = 1) OR ' .
            '(use_config_backorders = 0 AND backorders = 0)) AND ' .
            '((use_config_min_qty = 1 AND qty <= 1) OR ' .
            '(use_config_min_qty = 0 AND qty <= min_qty)) AND ' .
            'product_id IN (testString)';
        $this->stockConfigurationMock->expects(self::once())
            ->method('getDefaultScopeId')
            ->willReturn(0);
        $this->mockInitConfig();

        $this->selectMock->expects(self::once())
            ->method('from')
            ->with(self::identicalTo(self::PRODUCT_TABLE), 'entity_id')
            ->willReturnSelf();
        $this->selectMock->expects(self::once())
            ->method('where')
            ->with(self::identicalTo('type_id IN(?)'), self::identicalTo([1, 2]))
            ->willReturnSelf();
        $this->selectMock->expects(self::once())
            ->method('assemble')
            ->willReturn($string);

        $this->connectionMock->expects(self::once())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->connectionMock->expects(self::once())
            ->method('update')
            ->with(
                self::identicalTo(self::ITEM_TABLE),
                self::identicalTo(['is_in_stock' => 0, 'stock_status_changed_auto' => 1]),
                self::identicalTo($where)
            )->willReturnSelf();

        $this->stock->expects(self::once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->stock->expects(self::exactly(2))
            ->method('getTable')
            ->withConsecutive(
                [self::identicalTo('catalog_product_entity')],
                [self::identicalTo('cataloginventory_stock_item')]
            )->will(self::onConsecutiveCalls(
                self::PRODUCT_TABLE,
                self::ITEM_TABLE
            ));
        $this->stock->updateSetOutOfStock();
    }

    /**
     * Test set items in stock basing on their quantities and config settings.
     *
     * @return void
     */
    public function testUpdateSetInStock()
    {
        $website = 0;
        $string = 'testString';
        $where = 'website_id = 0 AND is_in_stock = 0 AND ' .
            'stock_status_changed_auto = 1 AND ' .
            '((use_config_manage_stock = 1 AND 1 = 1) OR ' .
            '(use_config_manage_stock = 0 AND manage_stock = 1)) AND ' .
            '((use_config_min_qty = 1 AND qty > 1) OR ' .
            '(use_config_min_qty = 0 AND qty > min_qty)) AND ' .
            'product_id IN (testString)';

        $this->stockConfigurationMock->expects(self::once())
            ->method('getDefaultScopeId')
            ->willReturn(0);

        $this->mockInitConfig();

        $this->connectionMock->expects(self::once())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->connectionMock->expects(self::once())
            ->method('update')
            ->with(
                self::identicalTo(self::ITEM_TABLE),
                self::identicalTo(['is_in_stock' => 1]),
                self::identicalTo($where)
            )->willReturnSelf();

        $this->selectMock->expects(self::once())
            ->method('from')
            ->with(self::identicalTo(self::PRODUCT_TABLE), 'entity_id')
            ->willReturnSelf();
        $this->selectMock->expects(self::once())
            ->method('where')
            ->with(self::identicalTo('type_id IN(?)'), self::identicalTo([1, 2]))
            ->willReturnSelf();
        $this->selectMock->expects(self::once())
            ->method('assemble')
            ->willReturn($string);

        $this->stock->expects(self::once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->stock->expects(self::exactly(2))
            ->method('getTable')
            ->withConsecutive(
                [self::identicalTo('catalog_product_entity')],
                [self::identicalTo('cataloginventory_stock_item')]
            )->will(self::onConsecutiveCalls(
                self::PRODUCT_TABLE,
                self::ITEM_TABLE
            ));

        $this->stock->updateSetInStock($website);
    }

    /**
     * Test update items low stock date basing on their quantities and config settings.
     *
     * @return void
     */
    public function testUpdateLowStockDate()
    {
        $website = 0;
        $gmtDate = 'testGmtDate';
        $currentDbTime = 'testCurrentDbTime';
        $conditionalDate = 'testConditionalDate';
        $where = 'website_id = 0 AND ((use_config_manage_stock = 1 AND ' .
            '1 = 1) OR (use_config_manage_stock = 0 AND manage_stock = 1)) AND product_id IN ()';
        $condition = 'testCondition';

        $this->stockConfigurationMock->expects(self::once())
            ->method('getDefaultScopeId')
            ->willReturn(0);

        $this->mockInitConfig();

        $this->connectionMock->expects(self::exactly(2))
            ->method('quoteInto')
            ->withConsecutive(
                [
                    self::identicalTo('(use_config_notify_stock_qty = 1 AND qty < ?)'),
                    self::identicalTo(1)
                ],
                [
                    self::identicalTo('?'),
                    self::identicalTo($gmtDate)
                ]
            )->will(self::onConsecutiveCalls($condition, $currentDbTime));
        $this->connectionMock->expects(self::once())
            ->method('getCheckSql')
            ->with(
                self::identicalTo($condition . ' OR (use_config_notify_stock_qty = 0 AND qty < notify_stock_qty)'),
                self::identicalTo($currentDbTime),
                self::identicalTo('NULL')
            )->willReturn($conditionalDate);
        $this->connectionMock->expects(self::once())
            ->method('select')
            ->willReturn($this->selectMock);
        //used anything for second argument because of new \Zend_Db_Expt used in code.
        $this->connectionMock->expects(self::once())
            ->method('update')
            ->with(
                self::identicalTo(self::ITEM_TABLE),
                self::anything(),
                self::identicalTo($where)
            )->willReturnSelf();

        $this->selectMock->expects(self::once())
            ->method('from')
            ->with(self::identicalTo(self::PRODUCT_TABLE), 'entity_id')
            ->willReturnSelf();
        $this->selectMock->expects(self::once())
            ->method('where')
            ->with(self::identicalTo('type_id IN(?)'), self::identicalTo([1, 2]))
            ->willReturnSelf();

        $this->dateTimeMock->expects(self::once())
            ->method('gmtDate')
            ->willReturn($gmtDate);

        $this->stock->expects(self::once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->stock->expects(self::exactly(2))
            ->method('getTable')
            ->withConsecutive(
                [self::identicalTo('catalog_product_entity')],
                [self::identicalTo('cataloginventory_stock_item')]
            )->will(self::onConsecutiveCalls(
                self::PRODUCT_TABLE,
                self::ITEM_TABLE
            ));

        $this->stock->updateLowStockDate($website);
    }

    /**
     * Test add low stock filter to product collection.
     *
     * @return void
     */
    public function testAddLowStockFilter()
    {
        $fields = ['testField1', 'testField2'];
        $qtyIf = new \Zend_Db_Expr('testQtyIf');
        $where = 'testWhere AND ((condition1 AND condition2 AND condition3) ' .
            'OR (condition4 AND condition5))';

        $this->mockInitConfig();

        /** @var \PHPUnit_Framework_MockObject_MockObject|Collection $productCollectionMock */
        $productCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollectionMock->expects(self::once())
            ->method('getSelect')
            ->willReturn($this->selectMock);
        $productCollectionMock->expects(self::once())
            ->method('joinTable')
            ->with(
                self::identicalTo(['invtr' => 'cataloginventory_stock_item']),
                self::identicalTo('product_id = entity_id'),
                self::identicalTo($fields),
                self::identicalTo($where)
            )->willReturnSelf();

        $this->selectMock->expects(self::once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects(self::once())
            ->method('getCheckSql')
            ->with(
                self::identicalTo('invtr.use_config_notify_stock_qty > 0'),
                1,
                'invtr.notify_stock_qty'
            )->willReturn($qtyIf);
        $this->connectionMock->expects(self::any())
            ->method('prepareSqlCondition')
            ->withConsecutive(
                [
                    self::identicalTo('invtr.use_config_manage_stock'),
                    self::identicalTo(1)
                ],
                [
                    self::identicalTo(1),
                    self::identicalTo(1)
                ],
                [
                    self::identicalTo('invtr.qty'),
                    self::identicalTo(['lt' => $qtyIf])
                ],
                [
                    self::identicalTo('invtr.use_config_manage_stock'),
                    self::identicalTo(0)
                ],
                [
                    self::identicalTo('invtr.manage_stock'),
                    self::identicalTo(1)
                ],
                [
                    self::identicalTo('invtr.low_stock_date'),
                    ['notnull' => true],
                ]
            )->will(self::onConsecutiveCalls(
                'condition1',
                'condition2',
                'condition3',
                'condition4',
                'condition5',
                'testWhere'
            ));

        $result = $this->stock->addLowStockFilter($productCollectionMock, $fields);
        self::assertInstanceOf(Stock::class, $result);
    }

    /**
     * Mock load some inventory configuration settings.
     *
     * @return void
     */
    private function mockInitConfig()
    {
        $value = '1';
        $configTypesIds = ['1' => '1', '2' => '2'];
        $this->scopeConfigMock->expects(self::exactly(4))
            ->method('getValue')
            ->withConsecutive(
                [
                    self::identicalTo(Configuration::XML_PATH_MANAGE_STOCK),
                    self::identicalTo(ScopeInterface::SCOPE_STORE)
                ],
                [
                    self::identicalTo(Configuration::XML_PATH_BACKORDERS),
                    self::identicalTo(ScopeInterface::SCOPE_STORE)
                ],
                [
                    self::identicalTo(Configuration::XML_PATH_MIN_QTY),
                    self::identicalTo(ScopeInterface::SCOPE_STORE)
                ],
                [
                    self::identicalTo(Configuration::XML_PATH_NOTIFY_STOCK_QTY),
                    self::identicalTo(ScopeInterface::SCOPE_STORE)
                ]
            )->willReturn($value);
        $this->stockConfigurationMock->expects(self::once())
            ->method('getIsQtyTypeIds')
            ->with(self::identicalTo(true))
            ->willReturn($configTypesIds);
    }
}
