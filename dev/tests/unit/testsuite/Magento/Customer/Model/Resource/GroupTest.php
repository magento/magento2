<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Resource;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Model\Resource\Group */
    protected $groupResourceModel;

    /** @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\Customer\Model\Vat|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerVat;

    /** @var \Magento\Customer\Model\Group|\PHPUnit_Framework_MockObject_MockObject */
    protected $groupModel;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $customersFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $groupManagement;

    protected function setUp()
    {
        $this->resource = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->customerVat = $this->getMock('Magento\Customer\Model\Vat', [], [], '', false);
        $this->customersFactory = $this->getMock(
            'Magento\Customer\Model\Resource\Customer\CollectionFactory',
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

        $this->groupResourceModel = (new ObjectManagerHelper($this))->getObject(
            'Magento\Customer\Model\Resource\Group',
            [
                'resource' => $this->resource,
                'groupManagement' => $this->groupManagement,
                'customersFactory' => $this->customersFactory,
            ]
        );
    }

    public function testDelete()
    {
        $dbAdapter = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface');
        $this->resource->expects($this->once())->method('getConnection')->will($this->returnValue($dbAdapter));

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
        $customerCollection = $this->getMock('Magento\Customer\Model\Resource\Customer\Collection', [], [], '', false);
        $customerCollection->expects($this->once())->method('addAttributeToFilter')->will($this->returnSelf());
        $customerCollection->expects($this->once())->method('load')->will($this->returnValue([$customer]));
        $this->customersFactory->expects($this->once())->method('create')
            ->will($this->returnValue($customerCollection));

        $this->groupResourceModel->delete($this->groupModel);
    }
}
