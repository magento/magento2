<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Test\Unit\Model\Resource\Quote;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\Resource\Quote\Collection
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readConnectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Model\Resource\Db\VersionControl\Snapshot
     */
    protected $entitySnapshotMock;

    protected function setUp()
    {
        $this->selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock->expects($this->any())
            ->method('from')
            ->withAnyParameters()
            ->willReturnSelf();

        $this->readConnectionMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->getMock();
        $this->readConnectionMock->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->resourceMock = $this->getMockBuilder('Magento\Quote\Model\Resource\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects($this->any())
            ->method('getReadConnection')
            ->willReturn($this->readConnectionMock);
        $this->resourceMock->expects($this->any())
            ->method('getMainTable')
            ->willReturn('test_table');
        $this->resourceMock->expects($this->any())
            ->method('getTable')
            ->willReturn('test_table');
        $this->customerResourceMock = $this->getMockBuilder('Magento\Customer\Model\Resource\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fetchStrategyMock = $this->getMockBuilder('Magento\Framework\Data\Collection\Db\FetchStrategy\Query')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityFactoryMock = $this->getMockBuilder('Magento\Framework\Data\Collection\EntityFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->entitySnapshotMock = $this->getMockBuilder('Magento\Framework\Model\Resource\Db\VersionControl\Snapshot')
            ->disableOriginalConstructor()
            ->setMethods(['registerSnapshot'])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            'Magento\Reports\Model\Resource\Quote\Collection',
            [
                'customerResource' => $this->customerResourceMock,
                'resource' => $this->resourceMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'entityFactory' => $this->entityFactoryMock,
                'entitySnapshot' => $this->entitySnapshotMock
            ]
        );
    }

    public function testResolveCustomerNames()
    {
        $customerName = "CONCAT_WS('firstname', 'lastname')";
        $customerTableName = 'customer_entity';
        $customerId = ['customer_id' => ['test_id']];
        $customersData = [['item_1']];
        $itemData = ['test'];

        $selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock->expects($this->any())
            ->method('getAdapter')
            ->willReturn($this->readConnectionMock);
        $selectMock->expects($this->once())
            ->method('from')
            ->with(['customer' => $customerTableName], ['email'])
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('columns')
            ->with(['customer_name' => $customerName])
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with('customer.entity_id IN (?)')
            ->willReturnSelf();

        $this->readConnectionMock->expects($this->once())
            ->method('getConcatSql')
            ->with(['firstname', 'lastname'], ' ')
            ->willReturn($customerName);

        $readConnectionMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->getMock();
        $readConnectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);

        $this->customerResourceMock->expects($this->once())
            ->method('getReadConnection')
            ->willReturn($readConnectionMock);
        $this->customerResourceMock->expects($this->once())
            ->method('getTable')
            ->with('customer_entity')
            ->willReturn($customerTableName);

        $this->readConnectionMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);
        $this->readConnectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($selectMock)
            ->willReturn($customersData);

        $this->fetchStrategyMock->expects($this->once())
            ->method('fetchAll')
            ->withAnyParameters()
            ->willReturn($customerId);

        $itemMock = $this->getMockBuilder('Magento\Framework\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->once())
            ->method('getData')
            ->willReturn($itemData);

        $this->entityFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);

        $this->assertNull($this->model->resolveCustomerNames());
    }
}
