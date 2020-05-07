<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Model;

use Magento\Customer\Model\Session;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CustomerTokenManagement;
use Magento\Vault\Model\PaymentTokenManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerTokenManagementTest extends TestCase
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

    protected function setUp(): void
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

    /**
     * @param int|null $customerId
     * @param bool $isLoggedCustomer
     * @return void
     * @dataProvider getCustomerSessionTokensNegativeDataProvider
     */
    public function testGetCustomerSessionTokensNegative($customerId, bool $isLoggedCustomer)
    {
        $this->customerSession->method('getCustomerId')->willReturn($customerId);
        $this->customerSession->method('isLoggedIn')->willReturn($isLoggedCustomer);
        $this->paymentTokenManagement->expects(static::never())->method('getVisibleAvailableTokens');

        static::assertEquals([], $this->tokenManagement->getCustomerSessionTokens());
    }

    /**
     * @return array
     */
    public function getCustomerSessionTokensNegativeDataProvider()
    {
        return [
            'not registered customer' => [null, false],
            'not logged in customer' => [1, false],
        ];
    }

    public function testGetCustomerSessionTokens()
    {
        $customerId = 1;
        $token = $this->getMockForAbstractClass(PaymentTokenInterface::class);
        $expectation = [$token];

        $this->customerSession->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->customerSession->expects(static::once())
            ->method('isLoggedIn')
            ->willReturn(true);

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
