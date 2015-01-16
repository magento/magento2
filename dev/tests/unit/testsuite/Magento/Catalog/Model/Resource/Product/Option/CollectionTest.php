<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Resource\Product\Option;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Core\Model\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var Value\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionsFactoryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    /**
     * @var \Zend_Db_Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMock(
            'Magento\Core\Model\EntityFactory', ['create'], [], '', false
        );
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->fetchStrategyMock = $this->getMock(
            'Magento\Framework\Data\Collection\Db\FetchStrategy\Query', ['fetchAll'], [], '', false
        );
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);
        $this->optionsFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Option\Value\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $this->resourceMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Option',
            ['getReadConnection', '__wakeup', 'getMainTable', 'getTable'],
            [],
            '',
            false
        );
        $this->selectMock = $this->getMock('Zend_Db_Select', ['from', 'reset'], [], '', false);
        $this->adapterMock =
            $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', ['select'], [], '', false);
        $this->adapterMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($this->selectMock));
        $this->resourceMock->expects($this->once())
            ->method('getReadConnection')
            ->will($this->returnValue($this->adapterMock));
        $this->resourceMock->expects($this->once())
            ->method('getMainTable')
            ->will($this->returnValue('test_main_table'));
        $this->resourceMock->expects($this->once())
            ->method('getTable')
            ->with('test_main_table')
            ->will($this->returnValue('test_main_table'));

        $this->collection = new Collection(
            $this->entityFactoryMock,
            $this->loggerMock,
            $this->fetchStrategyMock,
            $this->eventManagerMock,
            $this->optionsFactoryMock,
            $this->storeManagerMock,
            null,
            $this->resourceMock
        );
    }

    public function testReset()
    {
        $this->collection->reset();
    }
}
