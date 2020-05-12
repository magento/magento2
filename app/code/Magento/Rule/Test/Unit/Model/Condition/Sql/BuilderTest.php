<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rule\Test\Unit\Model\Condition\Sql;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Combine;
use Magento\Rule\Model\Condition\Sql\Builder;
use Magento\Rule\Model\Condition\Sql\Expression;
use Magento\Rule\Model\Condition\Sql\ExpressionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var Builder|MockObject
     */
    protected $_builder;

    protected function setUp(): void
    {
        $expressionMock = $this->createMock(Expression::class);
        $expressionFactory = $this->createPartialMock(
            ExpressionFactory::class,
            ['create']
        );
        $expressionFactory->expects($this->any())
            ->method('create')
            ->willReturn($expressionMock);
        $this->_builder = (new ObjectManagerHelper($this))->getObject(
            Builder::class,
            ['expressionFactory' => $expressionFactory]
        );
    }

    public function testAttachConditionToCollection()
    {
        $collection = $this->getMockBuilder(AbstractCollection::class)
            ->addMethods(['getStoreId', 'getDefaultStoreId'])
            ->onlyMethods(['getResource', 'getSelect'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $combine = $this->createPartialMock(Combine::class, ['getConditions']);
        $resource = $this->createPartialMock(Mysql::class, ['getConnection']);
        $select = $this->createPartialMock(Select::class, ['where']);
        $select->expects($this->never())
            ->method('where');

        $connection = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false
        );

        $collection->expects($this->once())
            ->method('getResource')
            ->willReturn($resource);
        $collection->expects($this->any())
            ->method('getSelect')
            ->willReturn($select);

        $resource->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $combine->expects($this->any())
            ->method('getConditions')
            ->willReturn([]);

        $this->_builder->attachConditionToCollection($collection, $combine);
    }

    /**
     * Test for attach condition to collection with operator in html format
     *
     * @covers \Magento\Rule\Model\Condition\Sql\Builder::attachConditionToCollection()
     * @return void;
     */
    public function testAttachConditionAsHtmlToCollection()
    {
        $abstractCondition = $this->getMockForAbstractClass(
            AbstractCondition::class,
            [],
            '',
            false,
            false,
            true,
            ['getOperatorForValidate', 'getMappedSqlField', 'getAttribute', 'getBindArgumentValue']
        );

        $abstractCondition->expects($this->once())->method('getMappedSqlField')->willReturn('argument');
        $abstractCondition->expects($this->once())->method('getOperatorForValidate')->willReturn('&gt;');
        $abstractCondition->expects($this->at(1))->method('getAttribute')->willReturn('attribute');
        $abstractCondition->expects($this->at(2))->method('getAttribute')->willReturn('attribute');
        $abstractCondition->expects($this->once())->method('getBindArgumentValue')->willReturn(10);

        $conditions = [$abstractCondition];
        $collection = $this->createPartialMock(
            AbstractCollection::class,
            [
                'getResource',
                'getSelect'
            ]
        );
        $combine = $this->getMockBuilder(Combine::class)
            ->addMethods(['getAggregator'])
            ->onlyMethods(['getConditions', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $resource = $this->createPartialMock(Mysql::class, ['getConnection']);
        $select = $this->createPartialMock(Select::class, ['where']);
        $select->expects($this->never())->method('where');

        $connection = $this->getMockForAbstractClass(
            AdapterInterface::class,
            ['quoteInto'],
            '',
            false
        );

        $connection->expects($this->once())->method('quoteInto')->with(' > ?', 10)->willReturn(' > 10');
        $collection->expects($this->once())->method('getResource')->willReturn($resource);
        $resource->expects($this->once())->method('getConnection')->willReturn($connection);
        $combine->expects($this->once())->method('getValue')->willReturn('attribute');
        $combine->expects($this->once())->method('getAggregator')->willReturn(' AND ');
        $combine->expects($this->at(0))->method('getConditions')->willReturn($conditions);
        $combine->expects($this->at(1))->method('getConditions')->willReturn($conditions);
        $combine->expects($this->at(2))->method('getConditions')->willReturn($conditions);
        $combine->expects($this->at(3))->method('getConditions')->willReturn($conditions);

        $this->_builder->attachConditionToCollection($collection, $combine);
    }
}
