<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Test\Unit\Model\Condition\Sql;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_builder;

    protected function setUp()
    {
        $expressionMock = $this->getMock(\Magento\Rule\Model\Condition\Sql\Expression::class, [], [], '', false);
        $expressionFactory = $this->getMock(
            \Magento\Rule\Model\Condition\Sql\ExpressionFactory::class,
            ['create'],
            [],
            '',
            false
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
        $collection = $this->getMock(
            \Magento\Eav\Model\Entity\Collection\AbstractCollection::class,
            ['getResource', 'getSelect'],
            [],
            '',
            false
        );
        $combine = $this->getMock(\Magento\Rule\Model\Condition\Combine::class, ['getConditions'], [], '', false);
        $resource = $this->getMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, ['getConnection'], [], '', false);
        $select = $this->getMock(\Magento\Framework\DB\Select::class, ['where'], [], '', false);
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
}
