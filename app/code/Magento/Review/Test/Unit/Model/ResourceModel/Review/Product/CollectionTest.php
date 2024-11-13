<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
    private $model;

    /**
     * @var MockObject
     */
    private $connectionMock;

    /**
     * @var MockObject
     */
    private $dbSelect;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->markTestSkipped('MAGETWO-59234: Code under the test depends on a virtual type which cannot be mocked.');

        $attribute = $this->createMock(AbstractAttribute::class);
        $eavConfig = $this->createMock(Config::class);
        $eavConfig->expects($this->any())->method('getAttribute')->willReturn($attribute);
        $this->dbSelect = $this->createMock(Select::class);
        $this->dbSelect->expects($this->any())->method('from')->willReturnSelf();
        $this->dbSelect->expects($this->any())->method('join')->willReturnSelf();
        $this->connectionMock = $this->createMock(
            Mysql::class
        );
        $this->connectionMock->expects($this->once())->method('select')->willReturn($this->dbSelect);
        $entity = $this->createMock(
            Product::class
        );
        $entity->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);
        $entity->expects($this->any())->method('getTable')->willReturn('table');
        $entity->expects($this->any())->method('getEntityTable')->willReturn('table');
        $entity->expects($this->any())->method('getDefaultAttributes')->willReturn([1 => 1]);
        $entity->expects($this->any())->method('getType')->willReturn('type');
        $entity->expects($this->any())->method('getEntityType')->willReturn('type');
        $universalFactory = $this->createMock(
            UniversalFactory::class
        );
        $universalFactory->expects($this->any())->method('create')->willReturn($entity);
        $store = $this->createMock(Store::class);
        $store->expects($this->any())->method('getId')->willReturn(1);
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $fetchStrategy = $this->createMock(
            Query::class
        );
        $fetchStrategy->expects($this->any())->method('fetchAll')->willReturn([]);
        $productLimitationMock = $this->createMock(
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
     * @param $attribute
     *
     * @return void
     * @dataProvider addAttributeToFilterDataProvider
     */
    public function testAddAttributeToFilter($attribute): void
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
            ->with($conditionSqlQuery)->willReturnSelf();
        $this->model->addAttributeToFilter($attribute, $condition);
    }

    /**
     * @return array
     */
    public static function addAttributeToFilterDataProvider(): array
    {
        return [
            ['rt.review_id'],
            ['rt.created_at'],
            ['rt.status_id'],
            ['rdt.title'],
            ['rdt.nickname'],
            ['rdt.detail']
        ];
    }

    /**
     * @return void
     */
    public function testAddAttributeToFilterWithAttributeStore(): void
    {
        $storeId = 1;
        $this->connectionMock->method('quoteInto')
            ->with('rt.review_id=store.review_id AND store.store_id = ?', $storeId)
            ->willReturn('sqlQuery');
        $this->model->addAttributeToFilter('stores', ['eq' => $storeId]);
        $this->model->load();
    }

    /**
     * @param $condition
     * @param $sqlConditionWith
     * @param $sqlConditionWithSec
     * @param $doubleConditionSqlQuery
     *
     * @return void
     * @dataProvider addAttributeToFilterWithAttributeTypeDataProvider
     */
    public function testAddAttributeToFilterWithAttributeType(
        $condition,
        $sqlConditionWith,
        $sqlConditionWithSec,
        $doubleConditionSqlQuery
    ): void {
        $conditionSqlQuery = 'sqlQuery';

        if ($sqlConditionWithSec) {
            $this->connectionMock->method('prepareSqlCondition')
                ->willReturnCallback(
                    function ($arg1, $arg2) use ($sqlConditionWith, $conditionSqlQuery, $sqlConditionWithSec) {
                        static $callCount = 0;
                        if ($callCount === 0 && $arg1 === 'rdt.customer_id' && $arg2 === $sqlConditionWith) {
                            $callCount++;
                            return $conditionSqlQuery;
                        } elseif ($callCount === 1 && $arg1 === 'rdt.store_id' && $arg2 === $sqlConditionWithSec) {
                            $callCount++;
                            return $conditionSqlQuery;
                        }
                    }
                );
        } else {
            $this->connectionMock->method('prepareSqlCondition')
                ->with('rdt.customer_id', $sqlConditionWith)
                ->willReturn($conditionSqlQuery);
        }
        $conditionSqlQuery = $doubleConditionSqlQuery
            ? $conditionSqlQuery . ' AND ' . $conditionSqlQuery
            : $conditionSqlQuery;
        $this->dbSelect
            ->expects($this->once())
            ->method('where')
            ->with($conditionSqlQuery)->willReturnSelf();
        $this->model->addAttributeToFilter('type', $condition);
    }

    /**
     * @return array
     */
    public static function addAttributeToFilterWithAttributeTypeDataProvider(): array
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
