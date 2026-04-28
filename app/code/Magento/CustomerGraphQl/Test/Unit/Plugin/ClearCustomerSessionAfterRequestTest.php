<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session\Proxy;
use Magento\CustomerGraphQl\Model\Context\AddUserInfoToContext;
use Magento\CustomerGraphQl\Plugin\ClearCustomerSessionAfterRequest;
use Magento\Framework\App\ResponseInterface;
use Magento\GraphQl\Controller\GraphQl;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see ClearCustomerSessionAfterRequest
 */
class ClearCustomerSessionAfterRequestTest extends TestCase
{
    /**
     * @var ClearCustomerSessionAfterRequest
     */
    private ClearCustomerSessionAfterRequest $clearCustomerSessionAfterRequest;

    /**
     * @var UserContextInterface|MockObject
     */
    private UserContextInterface $userContextMock;

    /**
     * @var CustomerRepository|MockObject
     */
    private CustomerRepository $customerRepositoryMock;

    /**
     * @var Proxy|MockObject
     */
    private Proxy $sessionMock;

    /**
     * @var AddUserInfoToContext|MockObject
     */
    private AddUserInfoToContext $addUserInfoToContextMock;

    /**
     * @var GraphQl|MockObject
     */
    private GraphQl $graphQlMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private ResponseInterface $responseMock;

    /**
     * @var CustomerInterface|MockObject
     */
    private CustomerInterface $customerMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->createMock(UserContextInterface::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepository::class);
        $this->sessionMock = $this->createMock(Proxy::class);
        $this->addUserInfoToContextMock = $this->createMock(AddUserInfoToContext::class);
        $this->graphQlMock = $this->createMock(GraphQl::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);
        $this->customerMock = $this->createMock(CustomerInterface::class);

        $this->clearCustomerSessionAfterRequest = new ClearCustomerSessionAfterRequest(
            $this->userContextMock,
            $this->sessionMock,
            $this->customerRepositoryMock,
            $this->addUserInfoToContextMock
        );
    }

    public function testAfterDispatchSkipsGuestRequests(): void
    {
        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->userContextMock->expects($this->never())
            ->method('getUserId');
        $this->addUserInfoToContextMock->expects($this->never())
            ->method('getLoggedInCustomerData');
        $this->sessionMock->expects($this->never())
            ->method('setCustomerId');
        $this->sessionMock->expects($this->never())
            ->method('setCustomerGroupId');

        $this->assertSame(
            $this->responseMock,
            $this->clearCustomerSessionAfterRequest->afterDispatch($this->graphQlMock, $this->responseMock)
        );
    }

    public function testAfterDispatchForLoggedInCustomer(): void
    {
        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn(1);
        $this->addUserInfoToContextMock
            ->expects($this->once())
            ->method('getLoggedInCustomerData')
            ->willReturn($this->customerMock);
        $this->customerMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->customerMock
            ->expects($this->once())
            ->method('getGroupId')
            ->willReturn(3);
        $this->sessionMock->expects($this->once())
            ->method('setCustomerId')
            ->with(1);
        $this->sessionMock->expects($this->once())
            ->method('setCustomerGroupId')
            ->with(3);

        $this->assertSame(
            $this->responseMock,
            $this->clearCustomerSessionAfterRequest->afterDispatch($this->graphQlMock, $this->responseMock)
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->clearCustomerSessionAfterRequest,
            $this->userContextMock,
            $this->customerRepositoryMock,
            $this->sessionMock,
            $this->addUserInfoToContextMock,
            $this->graphQlMock,
            $this->responseMock,
            $this->customerMock
        );
    }
}
