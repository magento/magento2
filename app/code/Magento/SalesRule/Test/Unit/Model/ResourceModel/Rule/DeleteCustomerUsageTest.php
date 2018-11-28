<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model\ResourceModel\Rule;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\DeleteCustomerUsage;
use Magento\SalesRule\Model\ResourceModel\Rule\Customer;

class DeleteCustomerUsageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Customer
     */
    protected $customerRuleMock;

    /**
     * @var AdapterInterface
     */
    protected $adapterMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->customerRuleMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     *  Execute when Updated Time Used equal to zero
     */
    public function testExecuteWithUpdateTimeUsedZero()
    {
        $this->adapterMock
            ->expects($this->once())
            ->method('delete')
            ->withAnyParameters()
            ->willReturn(1);

        $this->customerRuleMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->adapterMock);

        $this->customerRuleMock
            ->expects($this->once())
            ->method('getTable')
            ->with('salesrule_customer')
            ->willReturn('salesrule_customer');

        $deleteCustomerUsage = $this->objectManager->getObject(
            DeleteCustomerUsage::class,
            [
                'customerRuleDetails' => $this->customerRuleMock
            ]
        );

        $deleteCustomerUsage->execute(1, 1, 0);
    }

    /**
     * Execute When Updated Time Used Greater than Zero
     */
    public function testExecuteWithUpdateTimeGreaterThanZero()
    {
        $this->adapterMock->expects($this->never());
        $this->customerRuleMock->expects($this->never());
        $deleteCustomerUsage = $this->objectManager->getObject(
            DeleteCustomerUsage::class,
            [
                'customerRuleDetails' => $this->customerRuleMock
            ]
        );
        $deleteCustomerUsage->execute(1, 1, 1);
    }

    /**
     * Unset the Object
     */
    public function tearDown()
    {
        unset(
            $this->adapterMock,
            $this->customerRuleMock
        );
    }
}
