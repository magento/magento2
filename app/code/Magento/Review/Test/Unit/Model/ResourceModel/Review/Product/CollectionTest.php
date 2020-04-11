<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Model\ResourceModel\Review\Product;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Data\Collection\Db\FetchStrategy\Query;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Review\Model\ResourceModel\Review\Product\Collection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Collection
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $connectionMock;

    /**
     * @var MockObject
     */
    protected $dbSelect;

    protected function setUp(): void
    {
        $this->markTestSkipped('MAGETWO-59234: Code under the test depends on a virtual type which cannot be mocked.');

        $attribute = $this->getMock(AbstractAttribute::class, null, [], '', false);
        $eavConfig = $this->getMock(Config::class, ['getAttribute'], [], '', false);
        $eavConfig->expects($this->any())->method('getAttribute')->will($this->returnValue($attribute));
        $this->dbSelect = $this->getMock(Select::class, ['where', 'from', 'join'], [], '', false);
        $this->dbSelect->expects($this->any())->method('from')->will($this->returnSelf());
        $this->dbSelect->expects($this->any())->method('join')->will($this->returnSelf());
        $this->connectionMock = $this->getMock(
            Mysql::class,
            ['prepareSqlCondition', 'select', 'quoteInto'],
            [],
            '',
            false
        );
        $this->connectionMock->expects($this->once())->method('select')->will($this->returnValue($this->dbSelect));
        $entity = $this->getMock(
            Product::class,
            ['getConnection', 'getTable', 'getDefaultAttributes', 'getEntityTable', 'getEntityType', 'getType'],
            [],
            '',
            false
        );
        $entity->expects($this->once())->method('getConnection')->will($this->returnValue($this->connectionMock));
        $entity->expects($this->any())->method('getTable')->will($this->returnValue('table'));
        $entity->expects($this->any())->method('getEntityTable')->will($this->returnValue('table'));
        $entity->expects($this->any())->method('getDefaultAttributes')->will($this->returnValue([1 => 1]));
        $entity->expects($this->any())->method('getType')->will($this->returnValue('type'));
        $entity->expects($this->any())->method('getEntityType')->will($this->returnValue('type'));
        $universalFactory = $this->getMock(
            UniversalFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $universalFactory->expects($this->any())->method('create')->will($this->returnValue($entity));
        $store = $this->getMock(Store::class, ['getId'], [], '', false);
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $storeManager = $this->getMock(StoreManagerInterface::class);
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $fetchStrategy = $this->getMock(
            Query::class,
            ['fetchAll'],
            [],
            '',
            false
        );
        $fetchStrategy->expects($this->any())->method('fetchAll')->will($this->returnValue([]));
        $productLimitationMock = $this->getMock(
            ProductLimitation::class
        );
        $productLimitationFactoryMock = $this->getMockBuilder(ProductLimitationFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productLimitationFactoryMock->method('create')
            ->willReturn($productLimitationMock);
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            Collection::class,
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
            ->will($this->returnValue($conditionSqlQuery));
        $this->dbSelect
            ->expects($this->once())
            ->method('where')
            ->with($conditionSqlQuery)
            ->will($this->returnSelf());
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
            ->will($this->returnValue('sqlQuery'));
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
            ->will($this->returnValue($conditionSqlQuery));
        if ($sqlConditionWithSec) {
            $this->connectionMock
                ->expects($this->at(1))
                ->method('prepareSqlCondition')
                ->with('rdt.store_id', $sqlConditionWithSec)
                ->will($this->returnValue($conditionSqlQuery));
        }
        $conditionSqlQuery = $doubleConditionSqlQuery
            ? $conditionSqlQuery . ' AND ' . $conditionSqlQuery
            : $conditionSqlQuery;
        $this->dbSelect
            ->expects($this->once())
            ->method('where')
            ->with($conditionSqlQuery)
            ->will($this->returnSelf());
        $this->model->addAttributeToFilter('type', $condition);
    }

    /**
     * @return array
     */
    public function addAttributeToFilterWithAttributeTypeDataProvider()
    {
        $exprNull = new \Zend_Db_Expr('NULL');
        $defaultStore = Store::DEFAULT_STORE_ID;
        return [
            [1, ['is' => $exprNull], ['eq' => $defaultStore], true],
            [2, ['gt' => 0], null, false],
            [null, ['is' => $exprNull], ['neq' => $defaultStore], true]
        ];
    }
}
