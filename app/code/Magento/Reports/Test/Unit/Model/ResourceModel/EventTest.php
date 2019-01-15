<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Model\ResourceModel;

use Magento\Reports\Model\ResourceModel\Event;

class EventTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Event
     */
    protected $event;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMock();

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->getMock();

        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->contextMock
            ->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resourceMock);

        $this->event = new Event(
            $this->contextMock,
            $this->scopeConfigMock,
            $this->storeManagerMock
        );
    }

    /**
     * @return void
     */
    public function testUpdateCustomerTypeWithoutType()
    {
        $eventMock = $this->getMockBuilder(\Magento\Reports\Model\Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock
            ->expects($this->never())
            ->method('update');

        $this->event->updateCustomerType($eventMock, 1, 1);
    }

    /**
     * @return void
     */
    public function testUpdateCustomerTypeWithType()
    {
        $eventMock = $this->getMockBuilder(\Magento\Reports\Model\Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock
            ->expects($this->once())
            ->method('update');

        $this->event->updateCustomerType($eventMock, 1, 1, ['type']);
    }

    /**
     * @return void
     */
    public function testApplyLogToCollection()
    {
        $derivedSelect = 'SELECT * FROM table';
        $idFieldName = 'IdFieldName';

        $collectionSelectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['joinInner', 'order'])
            ->getMock();
        $collectionSelectMock
            ->expects($this->once())
            ->method('joinInner')
            ->with(
                ['evt' => new \Zend_Db_Expr("({$derivedSelect})")],
                "{$idFieldName} = evt.object_id",
                []
            )
            ->willReturnSelf();
        $collectionSelectMock
            ->expects($this->once())
            ->method('order')
            ->willReturnSelf();

        $collectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock
            ->expects($this->once())
            ->method('getResource')
            ->willReturnSelf();
        $collectionMock
            ->expects($this->once())
            ->method('getIdFieldName')
            ->willReturn($idFieldName);
        $collectionMock
            ->expects($this->any())
            ->method('getSelect')
            ->willReturn($collectionSelectMock);

        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['from', 'where', 'group', 'joinInner', '__toString'])
            ->getMock();
        $selectMock
            ->expects($this->once())
            ->method('from')
            ->willReturnSelf();
        $selectMock
            ->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('group')
            ->willReturnSelf();
        $selectMock
            ->expects($this->any())
            ->method('__toString')
            ->willReturn($derivedSelect);

        $this->connectionMock
            ->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);

        $this->storeMock
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->event->applyLogToCollection($collectionMock, 1, 1, 1);
    }

    /**
     * @return void
     */
    public function testClean()
    {
        $eventMock = $this->getMockBuilder(\Magento\Reports\Model\Event::class)
            ->disableOriginalConstructor()
            ->getMock();

        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['select', 'from', 'joinLeft', 'where', 'limit', 'fetchCol'])
            ->getMock();

        $this->connectionMock
            ->expects($this->at(1))
            ->method('fetchCol')
            ->willReturn(1);
        $this->connectionMock
            ->expects($this->any())
            ->method('delete');
        $this->connectionMock
            ->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $selectMock
            ->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $selectMock
            ->expects($this->any())
            ->method('joinLeft')
            ->willReturnSelf();
        $selectMock
            ->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $selectMock
            ->expects($this->any())
            ->method('limit')
            ->willReturnSelf();

        $this->event->clean($eventMock);
    }
}
