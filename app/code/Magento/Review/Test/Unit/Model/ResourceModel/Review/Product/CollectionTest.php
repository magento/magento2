<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Model\ResourceModel\Review\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\Product\Collection
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $dbSelect;

    protected function setUp(): void
    {
        $this->markTestSkipped('MAGETWO-59234: Code under the test depends on a virtual type which cannot be mocked.');

        $attribute = $this->getMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class, null, [], '', false);
        $eavConfig = $this->getMock(\Magento\Eav\Model\Config::class, ['getAttribute'], [], '', false);
        $eavConfig->expects($this->any())->method('getAttribute')->willReturn($attribute);
        $this->dbSelect = $this->getMock(\Magento\Framework\DB\Select::class, ['where', 'from', 'join'], [], '', false);
        $this->dbSelect->expects($this->any())->method('from')->willReturnSelf();
        $this->dbSelect->expects($this->any())->method('join')->willReturnSelf();
        $this->connectionMock = $this->getMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['prepareSqlCondition', 'select', 'quoteInto'],
            [],
            '',
            false
        );
        $this->connectionMock->expects($this->once())->method('select')->willReturn($this->dbSelect);
        $entity = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product::class,
            ['getConnection', 'getTable', 'getDefaultAttributes', 'getEntityTable', 'getEntityType', 'getType'],
            [],
            '',
            false
        );
        $entity->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);
        $entity->expects($this->any())->method('getTable')->willReturn('table');
        $entity->expects($this->any())->method('getEntityTable')->willReturn('table');
        $entity->expects($this->any())->method('getDefaultAttributes')->willReturn([1 => 1]);
        $entity->expects($this->any())->method('getType')->willReturn('type');
        $entity->expects($this->any())->method('getEntityType')->willReturn('type');
        $universalFactory = $this->getMock(
            \Magento\Framework\Validator\UniversalFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $universalFactory->expects($this->any())->method('create')->willReturn($entity);
        $store = $this->getMock(\Magento\Store\Model\Store::class, ['getId'], [], '', false);
        $store->expects($this->any())->method('getId')->willReturn(1);
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $fetchStrategy = $this->getMock(
            \Magento\Framework\Data\Collection\Db\FetchStrategy\Query::class,
            ['fetchAll'],
            [],
            '',
            false
        );
        $fetchStrategy->expects($this->any())->method('fetchAll')->willReturn([]);
        $productLimitationMock = $this->createMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation::class
        );
        $productLimitationFactoryMock = $this->getMockBuilder(ProductLimitationFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productLimitationFactoryMock->method('create')
            ->willReturn($productLimitationMock);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            \Magento\Review\Model\ResourceModel\Review\Product\Collection::class,
            [
                'universalFactory' => $universalFactory,
                'storeManager' => $storeManager,
                'eavConfig' => $eavConfig,
                'fetchStrategy' => $fetchStrategy,
                'productLimitationFactory' => $productLimitationFactoryMock
            ]
        );
    }

    /**
     * @dataProvider addAttributeToFilterDataProvider
     * @param $attribute
     */
    public function testAddAttributeToFilter($attribute)
    {
        $conditionSqlQuery = 'sqlQuery';
        $condition = ['eq' => 'value'];
        $this->connectionMock
            ->expects($this->once())
            ->method('prepareSqlCondition')
            ->with($attribute, $condition)
            ->willReturn($conditionSqlQuery);
        $this->dbSelect
            ->expects($this->once())
            ->method('where')
            ->with($conditionSqlQuery)
            ->willReturnSelf();
        $this->model->addAttributeToFilter($attribute, $condition);
    }

    /**
     * @return array
     */
    public function addAttributeToFilterDataProvider()
    {
        return [
            ['rt.review_id'],
            ['rt.created_at'],
            ['rt.status_id'],
            ['rdt.title'],
            ['rdt.nickname'],
            ['rdt.detail'],

        ];
    }

    public function testAddAttributeToFilterWithAttributeStore()
    {
        $storeId = 1;
        $this->connectionMock
            ->expects($this->at(0))
            ->method('quoteInto')
            ->with('rt.review_id=store.review_id AND store.store_id = ?', $storeId)
            ->willReturn('sqlQuery');
        $this->model->addAttributeToFilter('stores', ['eq' => $storeId]);
        $this->model->load();
    }

    /**
     * @dataProvider addAttributeToFilterWithAttributeTypeDataProvider
     * @param $condition
     * @param $sqlConditionWith
     * @param $sqlConditionWithSec
     * @param $doubleConditionSqlQuery
     */
    public function testAddAttributeToFilterWithAttributeType(
        $condition,
        $sqlConditionWith,
        $sqlConditionWithSec,
        $doubleConditionSqlQuery
    ) {
        $conditionSqlQuery = 'sqlQuery';
        $this->connectionMock
            ->expects($this->at(0))
            ->method('prepareSqlCondition')
            ->with('rdt.customer_id', $sqlConditionWith)
            ->willReturn($conditionSqlQuery);
        if ($sqlConditionWithSec) {
            $this->connectionMock
                ->expects($this->at(1))
                ->method('prepareSqlCondition')
                ->with('rdt.store_id', $sqlConditionWithSec)
                ->willReturn($conditionSqlQuery);
        }
        $conditionSqlQuery = $doubleConditionSqlQuery
            ? $conditionSqlQuery . ' AND ' . $conditionSqlQuery
            : $conditionSqlQuery;
        $this->dbSelect
            ->expects($this->once())
            ->method('where')
            ->with($conditionSqlQuery)
            ->willReturnSelf();
        $this->model->addAttributeToFilter('type', $condition);
    }

    /**
     * @return array
     */
    public function addAttributeToFilterWithAttributeTypeDataProvider()
    {
        $exprNull = new \Zend_Db_Expr('NULL');
        $defaultStore = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        return [
            [1, ['is' => $exprNull], ['eq' => $defaultStore], true],
            [2, ['gt' => 0], null, false],
            [null, ['is' => $exprNull], ['neq' => $defaultStore], true]
        ];
    }
}
