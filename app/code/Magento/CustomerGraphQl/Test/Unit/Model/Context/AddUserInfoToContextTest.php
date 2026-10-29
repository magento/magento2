<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\Model\Context;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session;
use Magento\CustomerGraphQl\Model\Context\AddUserInfoToContext;
use Magento\GraphQl\Model\Query\ContextParametersInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddUserInfoToContextTest extends TestCase
{
    /**
     * @var UserContextInterface|MockObject
     */
    private UserContextInterface $userContextMock;

    /**
     * @var CustomerRepository|MockObject
     */
    private CustomerRepository $customerRepositoryMock;

    /**
     * @var Session|MockObject
     */
    private Session $sessionMock;

    /**
     * @var Share|MockObject
     */
    private Share $configShareMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface $storeManagerMock;

    /**
     * @var ContextParametersInterface|MockObject
     */
    private ContextParametersInterface $contextParametersMock;

    /**
     * @var AddUserInfoToContext
     */
    private AddUserInfoToContext $addUserInfoToContext;

    protected function setUp(): void
    {
        $this->userContextMock = $this->createMock(UserContextInterface::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepository::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->configShareMock = $this->createMock(Share::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->contextParametersMock = $this->createMock(ContextParametersInterface::class);
        $this->addUserInfoToContext = new AddUserInfoToContext(
            $this->userContextMock,
            $this->sessionMock,
            $this->customerRepositoryMock,
            $this->configShareMock,
            $this->storeManagerMock
        );
    }

    public function testExecuteStoresCustomerDataInRequestLocalSession(): void
    {
        $customerMock = $this->createMock(CustomerInterface::class);

        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn(1);
        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->contextParametersMock->expects($this->once())
            ->method('setUserId')
            ->with(1);
        $this->contextParametersMock->expects($this->once())
            ->method('setUserType')
            ->with(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->contextParametersMock->expects($this->once())
            ->method('addExtensionAttribute')
            ->with('is_customer', true);
        $this->configShareMock->expects($this->once())
            ->method('isWebsiteScope')
            ->willReturn(false);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($customerMock);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn(3);
        $this->sessionMock->expects($this->once())
            ->method('setCustomerData')
            ->with($customerMock);
        $this->sessionMock->expects($this->once())
            ->method('setCustomerGroupId')
            ->with(3);

        $this->assertSame(
            $this->contextParametersMock,
            $this->addUserInfoToContext->execute($this->contextParametersMock)
        );
        $this->assertSame($customerMock, $this->addUserInfoToContext->getLoggedInCustomerData());
    }

    public function testExecuteClearsCustomerDataForGuest(): void
    {
        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn(0);
        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->contextParametersMock->expects($this->once())
            ->method('setUserId')
            ->with(0);
        $this->contextParametersMock->expects($this->once())
            ->method('setUserType')
            ->with(UserContextInterface::USER_TYPE_GUEST);
        $this->contextParametersMock->expects($this->once())
            ->method('addExtensionAttribute')
            ->with('is_customer', false);
        $this->configShareMock->expects($this->never())
            ->method('isWebsiteScope');
        $this->customerRepositoryMock->expects($this->never())
            ->method('getById');
        $this->sessionMock->expects($this->never())
            ->method('setCustomerData');
        $this->sessionMock->expects($this->never())
            ->method('setCustomerGroupId');

        $this->addUserInfoToContext->execute($this->contextParametersMock);

        $this->assertNull($this->addUserInfoToContext->getLoggedInCustomerData());
    }

    public function testGetLoggedInCustomerDataFallsBackToSessionState(): void
    {
        $customerMock = $this->createMock(CustomerInterface::class);

        $this->sessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->sessionMock->expects($this->once())
            ->method('getCustomerData')
            ->willReturn($customerMock);

        $this->assertSame($customerMock, $this->addUserInfoToContext->getLoggedInCustomerData());
    }
}
