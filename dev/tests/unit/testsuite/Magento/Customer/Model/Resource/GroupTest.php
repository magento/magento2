<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Model\Resource;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Model\Resource\Group */
    protected $groupResourceModel;

    /** @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\Customer\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerHelper;

    /** @var \Magento\Customer\Model\Group|\PHPUnit_Framework_MockObject_MockObject */
    protected $groupModel;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $customersFactory;

    protected function setUp()
    {
        $this->resource = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->customerHelper = $this->getMock('Magento\Customer\Helper\Data', [], [], '', false);
        $this->customersFactory = $this->getMock(
            'Magento\Customer\Model\Resource\Customer\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->groupModel = $this->getMock('Magento\Customer\Model\Group', [], [], '', false);

        $this->groupResourceModel = (new ObjectManagerHelper($this))->getObject(
            'Magento\Customer\Model\Resource\Group',
            [
                'resource' => $this->resource,
                'customerData' => $this->customerHelper,
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
        $defaultCustomerGroup = 1;
        $this->customerHelper->expects($this->once())->method('getDefaultCustomerGroupId')
            ->will($this->returnValue($defaultCustomerGroup));
        $customer->expects($this->once())->method('setGroupId')->with($defaultCustomerGroup);
        $customerCollection = $this->getMock('Magento\Customer\Model\Resource\Customer\Collection', [], [], '', false);
        $customerCollection->expects($this->once())->method('addAttributeToFilter')->will($this->returnSelf());
        $customerCollection->expects($this->once())->method('load')->will($this->returnValue([$customer]));
        $this->customersFactory->expects($this->once())->method('create')
            ->will($this->returnValue($customerCollection));

        $this->groupResourceModel->delete($this->groupModel);
    }
}
