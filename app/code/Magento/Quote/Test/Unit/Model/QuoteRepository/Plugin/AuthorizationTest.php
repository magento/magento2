<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\QuoteRepository\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository\Plugin\Authorization;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * @var MockObject|UserContextInterface
     */
    private $userContextMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);
        $this->authorization = new Authorization($this->userContextMock);
    }

    public function testAfterGetActiveThrowsExceptionIfQuoteIsNotAllowedForCurrentUserContext()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No such entity');
        // Quote without customer ID
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->userContextMock->expects($this->any())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->expects($this->any())->method('getUserId')->willReturn(1);
        $quoteMock->expects($this->exactly(2))->method('getCustomerId')->willReturn(2);
        $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock);
    }

    public function testAfterGetActiveReturnsQuoteIfQuoteIsAllowedForCurrentUserContext()
    {
        $quoteMock = $this->createMock(Quote::class);
        $quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->userContextMock->expects($this->any())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->assertEquals($quoteMock, $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock));
    }

    public function testAfterGetActiveForCustomerThrowsExceptionIfQuoteIsNotAllowedForCurrentUserContext()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No such entity');
        // Quote without customer ID
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->userContextMock->expects($this->any())->method('getUserType')->willReturn(
            UserContextInterface::USER_TYPE_CUSTOMER
        );
        $quoteMock->expects($this->exactly(2))->method('getCustomerId')->willReturn(2);
        $this->userContextMock->expects($this->any())->method('getUserId')->willReturn(1);
        $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock);
    }

    public function testAfterGetActiveForCustomerReturnsQuoteIfQuoteIsAllowedForCurrentUserContext()
    {
        $quoteMock = $this->createMock(Quote::class);
        $quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->userContextMock->expects($this->any())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->assertEquals($quoteMock, $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock));
    }
}
