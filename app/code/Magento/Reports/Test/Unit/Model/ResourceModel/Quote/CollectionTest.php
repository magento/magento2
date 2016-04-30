<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Test\Unit\Model\ResourceModel\Quote;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Quote\Collection
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

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
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot
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

        $this->connectionMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->resourceMock = $this->getMockBuilder('Magento\Quote\Model\ResourceModel\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->any())
            ->method('getMainTable')
            ->willReturn('test_table');
        $this->resourceMock->expects($this->any())
            ->method('getTable')
            ->willReturn('test_table');
        $this->customerResourceMock = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fetchStrategyMock = $this->getMockBuilder('Magento\Framework\Data\Collection\Db\FetchStrategy\Query')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityFactoryMock = $this->getMockBuilder('Magento\Framework\Data\Collection\EntityFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $snapshotClassName = 'Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot';
        $this->entitySnapshotMock = $this->getMockBuilder($snapshotClassName)
            ->disableOriginalConstructor()
            ->setMethods(['registerSnapshot'])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            'Magento\Reports\Model\ResourceModel\Quote\Collection',
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
        $customersData = [['entity_id' => 'test_id', 'name' => 'item_1']];

        $this->selectMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->selectMock->expects($this->once())
            ->method('from')
            ->with(['customer' => $customerTableName], ['entity_id', 'email'])
            ->willReturnSelf();
        $this->selectMock->expects($this->once())
            ->method('columns')
            ->with(['customer_name' => $customerName])
            ->willReturnSelf();
        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('customer.entity_id IN (?)')
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('getConcatSql')
            ->with(['firstname', 'lastname'], ' ')
            ->willReturn($customerName);

        $this->customerResourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->customerResourceMock->expects($this->once())
            ->method('getTable')
            ->with('customer_entity')
            ->willReturn($customerTableName);

        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->selectMock)
            ->willReturn($customersData);

        $this->fetchStrategyMock->expects($this->once())
            ->method('fetchAll')
            ->withAnyParameters()
            ->willReturn($customerId);

        $itemMock = $this->getMockBuilder('Magento\Framework\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);

        $this->assertNull($this->model->resolveCustomerNames());
    }
}
