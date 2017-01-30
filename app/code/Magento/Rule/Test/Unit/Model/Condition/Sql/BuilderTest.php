<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $expressionMock = $this->getMock('\Magento\Rule\Model\Condition\Sql\Expression', [], [], '', false);
        $expressionFactory = $this->getMock(
            '\Magento\Rule\Model\Condition\Sql\ExpressionFactory',
            ['create'],
            [],
            '',
            false
        );
        $expressionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($expressionMock));
        $this->_builder = (new ObjectManagerHelper($this))->getObject(
            '\Magento\Rule\Model\Condition\Sql\Builder',
            ['expressionFactory' => $expressionFactory]
        );
    }

    public function testAttachConditionToCollection()
    {
        $collection = $this->getMock(
            '\Magento\Eav\Model\Entity\Collection\AbstractCollection',
            ['getResource', 'getSelect'],
            [],
            '',
            false
        );
        $combine = $this->getMock('\Magento\Rule\Model\Condition\Combine', ['getConditions'], [], '', false);
        $resource = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', ['getConnection'], [], '', false);
        $select = $this->getMock('\Magento\Framework\DB\Select', ['where'], [], '', false);
        $select->expects($this->never())
            ->method('where');

        $connection = $this->getMockForAbstractClass('\Magento\Framework\DB\Adapter\AdapterInterface', [], '', false);

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
