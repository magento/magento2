<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Unit\Model\ResourceModel\Review\Summary;

use \Magento\Review\Model\ResourceModel\Review\Summary\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategy\Query|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    protected function setUp()
    {
        $this->fetchStrategyMock = $this->getMock(
            'Magento\Framework\Data\Collection\Db\FetchStrategy\Query',
            ['fetchAll'],
            [],
            '',
            false
        );
        $this->entityFactoryMock = $this->getMock(
            'Magento\Framework\Data\Collection\EntityFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->resourceMock = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\AbstractDb')
            ->setMethods(['getConnection', 'getMainTable', 'getTable'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->connectionMock = $this->getMock(
            '\Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['select', 'query'],
            [],
            '',
            false
        );
        $this->selectMock = $this->getMock(
            'Magento\Framework\DB\Select',
            ['from'],
            ['adapter' => $this->connectionMock]
        );
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($this->selectMock));
        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($this->connectionMock));
        $this->resourceMock->expects($this->once())
            ->method('getMainTable')
            ->willReturn('main_table_name');

        $this->resourceMock->expects($this->once())
            ->method('getTable')
            ->will($this->returnArgument(0));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->collection = $objectManager->getObject(
            'Magento\Review\Model\ResourceModel\Review\Summary\Collection',
            [
                'entityFactory' => $this->entityFactoryMock,
                'logger' => $this->loggerMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'resource' => $this->resourceMock
            ]
        );
    }

    public function testFetchItem()
    {
        $data = [1 => 'test'];
        $statementMock = $this->getMock('Zend_Db_Statement_Pdo', ['fetch'], [], '', false);
        $statementMock->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue($data));

        $this->connectionMock->expects($this->once())
            ->method('query')
            ->with($this->selectMock, $this->anything())
            ->will($this->returnValue($statementMock));

        $objectMock = $this->getMock('Magento\Framework\Model\AbstractModel', ['setData'], [], '', false);
        $objectMock->expects($this->once())
            ->method('setData')
            ->with($data);
        $this->entityFactoryMock->expects($this->once())
            ->method('create')
            ->with('Magento\Review\Model\Review\Summary')
            ->will($this->returnValue($objectMock));
        $item = $this->collection->fetchItem();

        $this->assertEquals($objectMock, $item);
        $this->assertEquals('id', $item->getIdFieldName());
    }

    public function testLoad()
    {
        $data = [10 => 'test'];
        $this->fetchStrategyMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->selectMock, [])
            ->will($this->returnValue([$data]));

        $objectMock = $this->getMock('Magento\Framework\DataObject', ['addData'], []);
        $objectMock->expects($this->once())
            ->method('addData')
            ->with($data);
        $this->entityFactoryMock->expects($this->once())
            ->method('create')
            ->with('Magento\Review\Model\Review\Summary')
            ->will($this->returnValue($objectMock));

        $this->collection->load();
    }
}
