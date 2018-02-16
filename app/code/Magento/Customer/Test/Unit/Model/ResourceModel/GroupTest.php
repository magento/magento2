<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class GroupTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->resource = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $this->customerVat = $this->getMock('Magento\Customer\Model\Vat', [], [], '', false);
        $this->customersFactory = $this->getMock(
            'Magento\Customer\Model\ResourceModel\Customer\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->groupManagement = $this->getMock(
            'Magento\Customer\Api\GroupManagementInterface',
            ['getDefaultGroup', 'getNotLoggedInGroup', 'isReadOnly', 'getLoggedInGroups', 'getAllCustomersGroup'],
            [],
            '',
            false);

        $this->groupModel = $this->getMock('Magento\Customer\Model\Group', [], [], '', false);

        $contextMock = $this->getMock('\Magento\Framework\Model\ResourceModel\Db\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resource);

        $this->relationProcessorMock = $this->getMock(
            '\Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor',
            [],
            [],
            '',
            false
        );

        $transactionManagerMock = $this->getMock('\Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface');
        $transactionManagerMock->expects($this->once())
            ->method('start')
            ->willReturn($this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface'));
        $contextMock->expects($this->once())
            ->method('getTransactionManager')
            ->willReturn($transactionManagerMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->relationProcessorMock);

        $this->groupResourceModel = (new ObjectManagerHelper($this))->getObject(
            'Magento\Customer\Model\ResourceModel\Group',
            [
                'context' => $contextMock,
                'groupManagement' => $this->groupManagement,
                'customersFactory' => $this->customersFactory,
            ]
        );
    }

    public function testDelete()
    {
        $dbAdapter = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface');
        $this->resource->expects($this->any())->method('getConnection')->will($this->returnValue($dbAdapter));

        $customer = $this->getMock(
            'Magento\Customer\Model\Customer',
            ['__wakeup', 'load', 'getId', 'getStoreId', 'setGroupId', 'save'],
            [],
            '',
            false
        );
        $customerId = 1;
        $customer->expects($this->once())->method('getId')->will($this->returnValue($customerId));
        $customer->expects($this->once())->method('load')->with($customerId)->will($this->returnSelf());
        $defaultCustomerGroup = $this->getMock(
            'Magento\Customer\Model\Group',
            ['getId'],
            [],
            '',
            false
        );
        $this->groupManagement->expects($this->once())->method('getDefaultGroup')
            ->will($this->returnValue($defaultCustomerGroup));
        $defaultCustomerGroup->expects($this->once())->method('getId')
            ->will($this->returnValue(1));
        $customer->expects($this->once())->method('setGroupId')->with(1);
        $customerCollection = $this->getMock('Magento\Customer\Model\ResourceModel\Customer\Collection', [], [], '', false);
        $customerCollection->expects($this->once())->method('addAttributeToFilter')->will($this->returnSelf());
        $customerCollection->expects($this->once())->method('load')->will($this->returnValue([$customer]));
        $this->customersFactory->expects($this->once())->method('create')
            ->will($this->returnValue($customerCollection));

        $this->relationProcessorMock->expects($this->once())->method('delete');
        $this->groupModel->expects($this->any())->method('getData')->willReturn(['data' => 'value']);
        $this->groupResourceModel->delete($this->groupModel);
    }
}
