<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Customer\Model\ResourceModel\Group */
    protected $groupResourceModel;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\Customer\Model\Vat|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerVat;

    /** @var \Magento\Customer\Model\Group|\PHPUnit_Framework_MockObject_MockObject */
    protected $groupModel;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $customersFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $groupManagement;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $relationProcessorMock;

    /**
     * @var Snapshot|\PHPUnit_Framework_MockObject_MockObject
     */
    private $snapshotMock;

    /**
     * Setting up dependencies.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->customerVat = $this->createMock(\Magento\Customer\Model\Vat::class);
        $this->customersFactory = $this->createPartialMock(\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory::class, ['create']);
        $this->groupManagement = $this->createPartialMock(\Magento\Customer\Api\GroupManagementInterface::class, ['getDefaultGroup', 'getNotLoggedInGroup', 'isReadOnly', 'getLoggedInGroups', 'getAllCustomersGroup']);

        $this->groupModel = $this->createMock(\Magento\Customer\Model\Group::class);

        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resource);

        $this->relationProcessorMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class);

        $this->snapshotMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class);

        $transactionManagerMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface::class
        );
        $transactionManagerMock->expects($this->any())
            ->method('start')
            ->willReturn($this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class));
        $contextMock->expects($this->once())
            ->method('getTransactionManager')
            ->willReturn($transactionManagerMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->relationProcessorMock);

        $this->groupResourceModel = (new ObjectManagerHelper($this))->getObject(
            \Magento\Customer\Model\ResourceModel\Group::class,
            [
                'context' => $contextMock,
                'groupManagement' => $this->groupManagement,
                'customersFactory' => $this->customersFactory,
                'entitySnapshot' => $this->snapshotMock
            ]
        );
    }

    /**
     * Test for save() method when we try to save entity with system's reserved ID.
     * 
     * @return void
     */
    public function testSaveWithReservedId()
    {
        $expectedId = 55;
        $this->snapshotMock->expects($this->once())->method('isModified')->willReturn(true);
        $this->snapshotMock->expects($this->once())->method('registerSnapshot')->willReturnSelf();

        $this->groupModel->expects($this->any())->method('getId')
            ->willReturn(\Magento\Customer\Model\Group::CUST_GROUP_ALL);
        $this->groupModel->expects($this->any())->method('getData')
            ->willReturn([]);
        $this->groupModel->expects($this->any())->method('isSaveAllowed')
            ->willReturn(true);
        $this->groupModel->expects($this->any())->method('getStoredData')
            ->willReturn([]);
        $this->groupModel->expects($this->once())->method('setId')
            ->with($expectedId);

        $dbAdapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'lastInsertId',
                    'describeTable',
                    'update',
                    'select'
                ]
            )
            ->getMockForAbstractClass();
        $dbAdapter->expects($this->any())->method('describeTable')->willReturn([]);
        $dbAdapter->expects($this->any())->method('update')->willReturnSelf();
        $dbAdapter->expects($this->once())->method('lastInsertId')->willReturn($expectedId);
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dbAdapter->expects($this->any())->method('select')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $this->resource->expects($this->any())->method('getConnection')->willReturn($dbAdapter);

        $this->groupResourceModel->save($this->groupModel);
    }

    /**
     * Test for delete() method when we try to save entity with system's reserved ID.
     *
     * @return void
     */
    public function testDelete()
    {
        $dbAdapter = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->resource->expects($this->any())->method('getConnection')->will($this->returnValue($dbAdapter));

        $customer = $this->createPartialMock(\Magento\Customer\Model\Customer::class, ['__wakeup', 'load', 'getId', 'getStoreId', 'setGroupId', 'save']);
        $customerId = 1;
        $customer->expects($this->once())->method('getId')->will($this->returnValue($customerId));
        $customer->expects($this->once())->method('load')->with($customerId)->will($this->returnSelf());
        $defaultCustomerGroup = $this->createPartialMock(\Magento\Customer\Model\Group::class, ['getId']);
        $this->groupManagement->expects($this->once())->method('getDefaultGroup')
            ->will($this->returnValue($defaultCustomerGroup));
        $defaultCustomerGroup->expects($this->once())->method('getId')
            ->will($this->returnValue(1));
        $customer->expects($this->once())->method('setGroupId')->with(1);
        $customerCollection = $this->createMock(\Magento\Customer\Model\ResourceModel\Customer\Collection::class);
        $customerCollection->expects($this->once())->method('addAttributeToFilter')->will($this->returnSelf());
        $customerCollection->expects($this->once())->method('load')->will($this->returnValue([$customer]));
        $this->customersFactory->expects($this->once())->method('create')
            ->will($this->returnValue($customerCollection));

        $this->relationProcessorMock->expects($this->once())->method('delete');
        $this->groupModel->expects($this->any())->method('getData')->willReturn(['data' => 'value']);
        $this->groupResourceModel->delete($this->groupModel);
    }
}
