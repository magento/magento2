<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Test\Unit\Model\Condition\Sql;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_builder;

    protected function setUp()
    {
        $expressionMock = $this->createMock(\Magento\Rule\Model\Condition\Sql\Expression::class);
        $expressionFactory = $this->createPartialMock(
            \Magento\Rule\Model\Condition\Sql\ExpressionFactory::class,
            ['create']
        );
        $expressionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($expressionMock));
        $this->_builder = (new ObjectManagerHelper($this))->getObject(
            \Magento\Rule\Model\Condition\Sql\Builder::class,
            ['expressionFactory' => $expressionFactory]
        );
    }

    public function testAttachConditionToCollection()
    {
        $collection = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Collection\AbstractCollection::class,
            [
                'getResource',
                'getSelect',
                'getStoreId',
                'getDefaultStoreId',
            ]
        );
        $combine = $this->createPartialMock(\Magento\Rule\Model\Condition\Combine::class, ['getConditions']);
        $resource = $this->createPartialMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, ['getConnection']);
        $select = $this->createPartialMock(\Magento\Framework\DB\Select::class, ['where']);
        $select->expects($this->never())
            ->method('where');

        $connection = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false
        );

        $collection->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($resource));
        $collection->expects($this->any())
            ->method('getSelect')
            ->will($this->returnValue($select));

        $resource->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $combine->expects($this->any())
            ->method('getConditions')
            ->will($this->returnValue([]));

        $this->_builder->attachConditionToCollection($collection, $combine);
    }

    /**
     * Test for attach condition to collection with operator in html format.
     *
     * @covers \Magento\Rule\Model\Condition\Sql\Builder::attachConditionToCollection()
     * @return void
     */
    public function testAttachConditionAsHtmlToCollection()
    {
        $abstractCondition = $this->getMockForAbstractClass(
            \Magento\Rule\Model\Condition\AbstractCondition::class,
            [],
            '',
            false,
            false,
            true,
            ['getOperatorForValidate', 'getMappedSqlField', 'getAttribute', 'getBindArgumentValue']
        );

        $abstractCondition->expects($this->once())->method('getMappedSqlField')->will($this->returnValue('argument'));
        $abstractCondition->expects($this->once())->method('getOperatorForValidate')->will($this->returnValue('&gt;'));
        $abstractCondition->expects($this->at(1))->method('getAttribute')->will($this->returnValue('attribute'));
        $abstractCondition->expects($this->at(2))->method('getAttribute')->will($this->returnValue('attribute'));
        $abstractCondition->expects($this->once())->method('getBindArgumentValue')->will($this->returnValue(10));

        $conditions = [$abstractCondition];
        $collection = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Collection\AbstractCollection::class,
            [
                'getResource',
                'getSelect',
            ]
        );
        $combine = $this->createPartialMock(
            \Magento\Rule\Model\Condition\Combine::class,
            [
                'getConditions',
                'getValue',
                'getAggregator',
            ]
        );

        $resource = $this->createPartialMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, ['getConnection']);
        $select = $this->createPartialMock(\Magento\Framework\DB\Select::class, ['where']);
        $select->expects($this->never())->method('where');

        $connection = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            ['quoteInto'],
            '',
            false
        );

        $connection->expects($this->once())->method('quoteInto')->with(' > ?', 10)->will($this->returnValue(' > 10'));
        $collection->expects($this->once())->method('getResource')->will($this->returnValue($resource));
        $resource->expects($this->once())->method('getConnection')->will($this->returnValue($connection));
        $combine->expects($this->once())->method('getValue')->willReturn('attribute');
        $combine->expects($this->once())->method('getAggregator')->willReturn(' AND ');
        $combine->expects($this->at(0))->method('getConditions')->will($this->returnValue($conditions));
        $combine->expects($this->at(1))->method('getConditions')->will($this->returnValue($conditions));
        $combine->expects($this->at(2))->method('getConditions')->will($this->returnValue($conditions));
        $combine->expects($this->at(3))->method('getConditions')->will($this->returnValue($conditions));

        $this->_builder->attachConditionToCollection($collection, $combine);
    }
}
