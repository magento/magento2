<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerGraphQl\Test\Unit\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session;
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
     * @var Session|MockObject
     */
    private Session $sessionMock;

    /**
     * @var CustomerRepository|MockObject
     */
    private CustomerRepository $customerRepositoryMock;

    /**
     * @var AddUserInfoToContext
     */
    private AddUserInfoToContext $addUserInfoToContextMock;

    /**
     * @var GraphQl
     */
    private GraphQl $graphQlMock;

    /**
     * @var ResponseInterface
     */
    private ResponseInterface $responseMock;

    /**
     * @var CustomerInterface|MockObject
     */
    private CustomerInterface $customerMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->createMock(UserContextInterface::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepository::class);
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

    /**
     * Test after dispatch plugin
     */
    public function testAfterDispatch(): void
    {
        $this->addUserInfoToContextMock
            ->expects($this->once())
            ->method('getLoggedInCustomerData');

        $this->clearCustomerSessionAfterRequest->afterDispatch($this->graphQlMock, $this->responseMock);
    }

    /**
     * Test after dispatch plugin for logged in customer
     */
    public function testAfterDispatchForLoggedInCustomer(): void
    {
        $this->addUserInfoToContextMock
            ->expects($this->once())
            ->method('getLoggedInCustomerData')
            ->willReturn($this->customerMock);
        $this->customerMock
            ->expects($this->once())
            ->method('getId');
        $this->customerMock
            ->expects($this->once())
            ->method('getGroupId');

        $this->clearCustomerSessionAfterRequest->afterDispatch($this->graphQlMock, $this->responseMock);
    }

    protected function tearDown(): void
    {
        unset(
            $this->clearCustomerSessionAfterRequest,
            $this->userContextMock,
            $this->sessionMock,
            $this->customerRepositoryMock,
            $this->addUserInfoToContextMock,
            $this->graphQlMock,
            $this->responseMock,
            $this->customerMock
        );
    }
}
