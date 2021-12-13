<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Group;
use Magento\Customer\Model\Vat;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupTest extends TestCase
{
    /** @var Group */
    protected $groupResourceModel;

    /** @var ResourceConnection|MockObject */
    protected $resource;

    /** @var Vat|MockObject */
    protected $customerVat;

    /** @var \Magento\Customer\Model\Group|MockObject */
    protected $groupModel;

    /** @var MockObject */
    protected $customersFactory;

    /** @var MockObject */
    protected $groupManagement;

    /** @var MockObject */
    protected $relationProcessorMock;

    /**
     * @var Snapshot|MockObject
     */
    private $snapshotMock;

    /**
     * Setting up dependencies.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->customerVat = $this->createMock(Vat::class);
        $this->customersFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->groupManagement = $this->getMockBuilder(GroupManagementInterface::class)
            ->onlyMethods(
                ['isReadOnly', 'getDefaultGroup', 'getNotLoggedInGroup', 'getLoggedInGroups', 'getAllCustomersGroup']
            )
            ->getMockForAbstractClass();

        $this->groupModel = $this->createMock(\Magento\Customer\Model\Group::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resource);

        $this->relationProcessorMock = $this->createMock(
            ObjectRelationProcessor::class
        );

        $this->snapshotMock = $this->createMock(
            Snapshot::class
        );

        $transactionManagerMock = $this->createMock(
            TransactionManagerInterface::class
        );
        $transactionManagerMock->expects($this->any())
            ->method('start')
            ->willReturn($this->getMockForAbstractClass(AdapterInterface::class));
        $contextMock->expects($this->once())
            ->method('getTransactionManager')
            ->willReturn($transactionManagerMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->relationProcessorMock);

        $this->groupResourceModel = (new ObjectManagerHelper($this))->getObject(
            Group::class,
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
        $this->groupModel->expects($this->once())->method('getCode')
            ->willReturn('customer_group_code');

        $dbAdapter = $this->getMockBuilder(AdapterInterface::class)
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
        $dbAdapter->expects($this->any())->method('describeTable')->willReturn(['customer_group_id' => []]);
        $dbAdapter->expects($this->any())->method('update')->willReturnSelf();
        $dbAdapter->expects($this->once())->method('lastInsertId')->willReturn($expectedId);
        $selectMock = $this->getMockBuilder(Select::class)
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
        $dbAdapter = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resource->expects($this->any())->method('getConnection')->willReturn($dbAdapter);

        $customer = $this->getMockBuilder(Customer::class)
            ->addMethods(['getStoreId', 'setGroupId'])
            ->onlyMethods(['__wakeup', 'load', 'getId', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerId = 1;
        $customer->expects($this->once())->method('getId')->willReturn($customerId);
        $customer->expects($this->once())->method('load')->with($customerId)->willReturnSelf();
        $defaultCustomerGroup = $this->createPartialMock(\Magento\Customer\Model\Group::class, ['getId']);
        $this->groupManagement->expects($this->once())->method('getDefaultGroup')
            ->willReturn($defaultCustomerGroup);
        $defaultCustomerGroup->expects($this->once())->method('getId')
            ->willReturn(1);
        $customer->expects($this->once())->method('setGroupId')->with(1);
        $customerCollection = $this->createMock(Collection::class);
        $customerCollection->expects($this->once())->method('addAttributeToFilter')->willReturnSelf();
        $customerCollection->expects($this->once())->method('load')->willReturn([$customer]);
        $this->customersFactory->expects($this->once())->method('create')
            ->willReturn($customerCollection);

        $this->relationProcessorMock->expects($this->once())->method('delete');
        $this->groupModel->expects($this->any())->method('getData')->willReturn(['data' => 'value']);
        $this->groupResourceModel->delete($this->groupModel);
    }
}
