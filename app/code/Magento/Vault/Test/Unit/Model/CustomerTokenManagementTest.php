<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model;

use Magento\Customer\Model\Session;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CustomerTokenManagement;
use Magento\Vault\Model\PaymentTokenManagement;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CustomerTokenManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaymentTokenManagement|MockObject
     */
    private $paymentTokenManagement;

    /**
     * @var Session|MockObject
     */
    private $customerSession;

    /**
     * @var CustomerTokenManagement
     */
    private $tokenManagement;

    protected function setUp()
    {
        $this->paymentTokenManagement = $this->getMockBuilder(PaymentTokenManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenManagement = new CustomerTokenManagement(
            $this->paymentTokenManagement,
            $this->customerSession
        );
    }

    public function testGetCustomerSessionTokensNonRegisteredCustomer()
    {
        $this->customerSession->expects(self::once())
            ->method('getCustomerId')
            ->willReturn(null);

        $this->paymentTokenManagement->expects(static::never())
            ->method('getVisibleAvailableTokens');

        $this->tokenManagement->getCustomerSessionTokens();
    }

    public function testGetCustomerSessionTokensForNotExistsCustomer()
    {
        $this->customerSession->expects(static::once())
            ->method('getCustomerId')
            ->willReturn(null);

        $this->paymentTokenManagement->expects(static::never())
            ->method('getVisibleAvailableTokens');

        $this->tokenManagement->getCustomerSessionTokens();
    }

    public function testGetCustomerSessionTokens()
    {
        $customerId = 1;
        $token = $this->getMock(PaymentTokenInterface::class);
        $expectation = [$token];

        $this->customerSession->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->paymentTokenManagement->expects(static::once())
            ->method('getVisibleAvailableTokens')
            ->with($customerId)
            ->willReturn($expectation);

        static::assertEquals(
            $expectation,
            $this->tokenManagement->getCustomerSessionTokens()
        );
    }
}
