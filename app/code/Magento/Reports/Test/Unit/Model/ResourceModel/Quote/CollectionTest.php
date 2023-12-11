<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Model\ResourceModel\Quote;

use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Framework\Data\Collection\Db\FetchStrategy\Query;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Reports\Model\ResourceModel\Quote\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $customerResourceMock;

    /**
     * @var MockObject
     */
    protected $connectionMock;

    /**
     * @var MockObject
     */
    protected $resourceMock;

    /**
     * @var MockObject
     */
    protected $selectMock;

    /**
     * @var MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var MockObject|Snapshot
     */
    protected $entitySnapshotMock;

    protected function setUp(): void
    {
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock->expects($this->any())
            ->method('from')
            ->withAnyParameters()
            ->willReturnSelf();

        $this->connectionMock = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->resourceMock = $this->getMockBuilder(Quote::class)
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
        $this->customerResourceMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fetchStrategyMock = $this->getMockBuilder(
            Query::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->entityFactoryMock = $this->getMockBuilder(EntityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $snapshotClassName = Snapshot::class;
        $this->entitySnapshotMock = $this->getMockBuilder($snapshotClassName)
            ->disableOriginalConstructor()
            ->setMethods(['registerSnapshot'])
            ->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            Collection::class,
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

        $itemMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($itemMock);

        $this->assertNull($this->model->resolveCustomerNames());
    }
}
