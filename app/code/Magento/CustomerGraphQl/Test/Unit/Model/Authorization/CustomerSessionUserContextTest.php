<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Model\Session;
use Magento\CustomerGraphQl\Model\Authorization\CustomerSessionUserContext;
use Magento\Framework\App\Request\Http;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerSessionUserContextTest extends TestCase
{
    /**
     * @var Session|MockObject
     */
    private Session $customerSessionMock;

    /**
     * @var Http|MockObject
     */
    private Http $requestMock;

    /**
     * @var CustomerSessionUserContext
     */
    private CustomerSessionUserContext $customerSessionUserContext;

    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->customerSessionUserContext = new CustomerSessionUserContext(
            $this->customerSessionMock,
            $this->requestMock
        );
    }

    public function testGetUserContextDoesNotStartSessionForGetRequest(): void
    {
        $this->requestMock->expects($this->once())
            ->method('isGet')
            ->willReturn(true);
        $this->requestMock->expects($this->never())
            ->method('getCookie');
        $this->customerSessionMock->expects($this->never())
            ->method('getId');

        $this->assertNull($this->customerSessionUserContext->getUserType());
        $this->assertNull($this->customerSessionUserContext->getUserId());
    }

    public function testGetUserContextUsesSessionForNonGetRequest(): void
    {
        $this->requestMock->expects($this->once())
            ->method('isGet')
            ->willReturn(false);
        $this->requestMock->expects($this->never())
            ->method('getCookie');
        $this->customerSessionMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->assertSame(UserContextInterface::USER_TYPE_CUSTOMER, $this->customerSessionUserContext->getUserType());
        $this->assertSame(1, $this->customerSessionUserContext->getUserId());
    }

    public function testResetStateClearsResolvedUserId(): void
    {
        $this->requestMock->expects($this->exactly(2))
            ->method('isGet')
            ->willReturn(false);
        $this->requestMock->expects($this->never())
            ->method('getCookie');
        $this->customerSessionMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturnOnConsecutiveCalls(2, 3);

        $this->assertSame(2, $this->customerSessionUserContext->getUserId());
        $this->customerSessionUserContext->_resetState();
        $this->assertSame(3, $this->customerSessionUserContext->getUserId());
    }
}
