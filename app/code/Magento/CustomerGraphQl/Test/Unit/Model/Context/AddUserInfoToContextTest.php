<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerGraphQl\Test\Unit\Model\Context;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session;
use Magento\CustomerGraphQl\Model\Context\AddUserInfoToContext;
use Magento\GraphQl\Model\Query\ContextParametersInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see AddUserInfoToContext
 */
class AddUserInfoToContextTest extends TestCase
{
    /**
     * @var AddUserInfoToContext
     */
    private AddUserInfoToContext $addUserInfoToContext;

    /**
     * @var UserContextInterface|MockObject
     */
    private UserContextInterface $userContextMock;

    /**
     * @var Session|MockObject
     */
    private Session $sessionMock;

    /**
     * @var CustomerRepository|MockObject
     */
    private CustomerRepository $customerRepositoryMock;

    /**
     * @var ContextParametersInterface|MockObject
     */
    private ContextParametersInterface $contextParametersMock;

    /**
     * @var CustomerInterface|MockObject
     */
    private CustomerInterface $customerMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->createMock(UserContextInterface::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepository::class);
        $this->contextParametersMock = $this->createMock(ContextParametersInterface::class);
        $this->customerMock = $this->createMock(CustomerInterface::class);

        $this->addUserInfoToContext = new AddUserInfoToContext(
            $this->userContextMock,
            $this->sessionMock,
            $this->customerRepositoryMock
        );
    }

    /**
     * Test execute function for user type - customer
     */
    public function testExecuteForCustomer(): void
    {
        $this->userContextMock
            ->expects($this->once())
            ->method('getUserId')
            ->willReturn(10);
        $this->contextParametersMock
            ->expects($this->once())
            ->method('setUserId');
        $this->userContextMock
            ->expects($this->once())
            ->method('getUserType')
            ->willReturn(3);
        $this->contextParametersMock
            ->expects($this->once())
            ->method('setUserType');
        $this->sessionMock
            ->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->sessionMock
            ->expects($this->once())
            ->method('getCustomerData')
            ->willReturn($this->customerMock);
        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->willReturn($this->customerMock);
        $this->sessionMock
            ->expects($this->once())
            ->method('setCustomerData');
        $this->sessionMock
            ->expects($this->once())
            ->method('setCustomerGroupId');
        $this->addUserInfoToContext->execute($this->contextParametersMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->addCustomerGroupToContext,
            $this->userContextMock,
            $this->sessionMock,
            $this->customerRepositoryMock,
            $this->contextParametersMock,
            $this->customerMock
        );
    }
}
