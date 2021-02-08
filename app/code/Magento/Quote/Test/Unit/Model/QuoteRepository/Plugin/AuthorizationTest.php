<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\QuoteRepository\Plugin;

use Magento\Quote\Model\QuoteRepository\Plugin\Authorization;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class AuthorizationTest
 */
class AuthorizationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository\Plugin\Authorization
     */
    private $authorization;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Authorization\Model\UserContextInterface
     */
    private $userContextMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->authorization = new Authorization($this->userContextMock);
    }

    /**
     */
    public function testAfterGetActiveThrowsExceptionIfQuoteIsNotAllowedForCurrentUserContext()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('No such entity');

        // Quote without customer ID
        $quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getCustomerId']);
        $quoteRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->userContextMock->expects($this->any())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->expects($this->any())->method('getUserId')->willReturn(1);
        $quoteMock->expects($this->exactly(2))->method('getCustomerId')->willReturn(2);
        $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock);
    }

    public function testAfterGetActiveReturnsQuoteIfQuoteIsAllowedForCurrentUserContext()
    {
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quoteRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->userContextMock->expects($this->any())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->assertEquals($quoteMock, $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock));
    }

    /**
     */
    public function testAfterGetActiveForCustomerThrowsExceptionIfQuoteIsNotAllowedForCurrentUserContext()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('No such entity');

        // Quote without customer ID
        $quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getCustomerId']);
        $quoteRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->userContextMock->expects($this->any())->method('getUserType')->willReturn(
            UserContextInterface::USER_TYPE_CUSTOMER
        );
        $quoteMock->expects($this->exactly(2))->method('getCustomerId')->willReturn(2);
        $this->userContextMock->expects($this->any())->method('getUserId')->willReturn(1);
        $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock);
    }

    public function testAfterGetActiveForCustomerReturnsQuoteIfQuoteIsAllowedForCurrentUserContext()
    {
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quoteRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->userContextMock->expects($this->any())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);
        $this->assertEquals($quoteMock, $this->authorization->afterGetActive($quoteRepositoryMock, $quoteMock));
    }
}
